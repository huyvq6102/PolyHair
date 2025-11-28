<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process payment for the given cart and user.
     *
     * @param \App\Models\User $user
     * @param array $cart
     * @param string $paymentMethod
     * @return \App\Models\Payment
     * @throws Exception
     */
    public function processPayment($user, $cart, $paymentMethod = 'cash')
    {
        DB::beginTransaction();

        try {
            $total = 0;
            $appointmentId = null;
            $orderId = null;
            $orderItems = [];

            // Note: Currently logic only supports linking payment to the LAST processed appointment.
            // If cart has multiple independent items, this might need review in the future.

            foreach ($cart as $key => $item) {
                // -------------------------
                // SERVICE VARIANT
                // -------------------------
                if ($item['type'] === 'service_variant') {
                    $variant = ServiceVariant::with('service')->find($item['id']);
                    
                    if (!$variant || !$variant->service) {
                        continue; 
                    }
                    
                    $quantity = $item['quantity'] ?? 1;
                    $price = $variant->price * $quantity;

                    // Create a new appointment for this service
                    $appointment = Appointment::create([
                        'user_id'    => $user->id,
                        'status'     => 'Đã thanh toán',
                        'start_at'   => now(),
                        'end_at'     => now()->addMinutes($variant->duration),
                    ]);

                    $appointmentId = $appointment->id;

                    AppointmentDetail::create([
                        'appointment_id'      => $appointment->id,
                        'service_variant_id'  => $variant->id,
                        'price_snapshot'      => $variant->price,
                        'duration'            => $variant->duration,
                        'status'              => 'Hoàn thành',
                    ]);

                    $total += $price;
                }

                // -------------------------
                // APPOINTMENT
                // -------------------------
                if ($item['type'] === 'appointment') {
                    $appointment = Appointment::with('appointmentDetails.serviceVariant.service')
                        ->find($item['id']);

                    if ($appointment) {
                        $appointmentId = $appointment->id;
                        $appointmentTotal = 0;

                        foreach ($appointment->appointmentDetails as $detail) {
                            if (!$detail->serviceVariant || !$detail->serviceVariant->service) {
                                continue;
                            }

                            $price = $detail->price_snapshot ?? $detail->serviceVariant->price;
                            $appointmentTotal += $price;
                        }

                        $total += $appointmentTotal;

                        $appointment->status = 'Đã thanh toán';
                        $appointment->save();
                    }
                }

                // -------------------------
                // PRODUCT
                // -------------------------
                if ($item['type'] === 'product') {
                    $product = Product::find($item['id']);
                    if ($product) {
                        $quantity = $item['quantity'] ?? 1;
                        $price = $product->price; // Assuming 'price' column exists in products table
                        
                        $orderItems[] = [
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'price' => $price
                        ];

                        $total += $price * $quantity;
                    }
                }
            }

            // Create Order if there are products
            if (!empty($orderItems)) {
                $order = Order::create([
                    'id_user' => $user->id,
                    'status' => 'Đã thanh toán',
                    'address' => $user->address ?? 'Tại cửa hàng', 
                    'phone' => $user->phone ?? '',
                ]);

                $orderId = $order->id;

                foreach ($orderItems as $orderItem) {
                    OrderDetail::create([
                        'id_order' => $order->id,
                        'id_product' => $orderItem['product_id'],
                        'quantity' => $orderItem['quantity'],
                        'price' => $orderItem['price']
                    ]);
                }
            }

            // VAT Calculation
            $VAT = $total * 0.1;
            $grandTotal = $total + $VAT;

            // Create Payment Record
            $payment = Payment::create([
                'user_id'        => $user->id,
                'appointment_id' => $appointmentId,
                'order_id'       => $orderId,
                'invoice_code'   => $this->generateInvoiceCode(),
                'price'          => $total,
                'VAT'            => $VAT,
                'total'          => $grandTotal,
                'created_by'     => $user->name,
                'payment_type'   => $paymentMethod,
            ]);

            DB::commit();

            return $payment;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Payment Processing Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a unique invoice code.
     * Format: INV-YYYYMMDD-XXXXXX
     */
    protected function generateInvoiceCode()
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        
        do {
            $randomString = strtoupper(Str::random(6));
            $code = $prefix . $randomString;
        } while (Payment::where('invoice_code', $code)->exists());

        return $code;
    }
}
