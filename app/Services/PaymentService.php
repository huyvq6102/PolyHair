<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentDetail;
use App\Models\Combo;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionUsage;
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
     * @param string|null $couponCode
     * @return \App\Models\Payment
     * @throws Exception
     */
    public function processPayment($user, $cart, $paymentMethod = 'cash', $couponCode = null)
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
                if (isset($item['type']) && $item['type'] === 'service_variant') {
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
                // COMBO
                // -------------------------
                if (isset($item['type']) && $item['type'] === 'combo') {
                    $combo = Combo::find($item['id']);
                    
                    if ($combo) {
                        $quantity = $item['quantity'] ?? 1;
                        $price = $combo->price * $quantity;

                        // Create a new appointment for this combo
                        // Note: Combos might not have a specific duration field, defaulting to 60 mins or 0
                        $appointment = Appointment::create([
                            'user_id'    => $user->id,
                            'status'     => 'Đã thanh toán',
                            'start_at'   => now(),
                            'end_at'     => now()->addMinutes(60), 
                        ]);

                        $appointmentId = $appointment->id;

                        AppointmentDetail::create([
                            'appointment_id'      => $appointment->id,
                            'combo_id'            => $combo->id,
                            'price_snapshot'      => $combo->price,
                            'duration'            => 60, // Default
                            'status'              => 'Hoàn thành',
                        ]);

                        $total += $price;
                    }
                }

                // -------------------------
                // APPOINTMENT
                // -------------------------
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    $appointment = Appointment::with('appointmentDetails.serviceVariant.service')
                        ->find($item['id']);

                    if ($appointment) {
                        $appointmentId = $appointment->id;
                        $appointmentTotal = 0;

                        foreach ($appointment->appointmentDetails as $detail) {
                            // Calculate price based on snapshot or variant or combo
                            if ($detail->price_snapshot) {
                                $price = $detail->price_snapshot;
                            } elseif ($detail->serviceVariant) {
                                $price = $detail->serviceVariant->price;
                            } elseif ($detail->combo) {
                                $price = $detail->combo->price;
                            } else {
                                $price = 0;
                            }

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
                if (isset($item['type']) && $item['type'] === 'product') {
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

            // -------------------------
            // PROMOTION CALCULATION
            // -------------------------
            $discountAmount = 0;
            $appliedPromotion = null;

            if ($couponCode) {
                $appliedPromotion = Promotion::where('code', $couponCode)
                    ->where('status', 1)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                if ($appliedPromotion) {
                    $discountAmount = $total * ($appliedPromotion->discount_percent / 100);
                }
            }

            $taxablePrice = max(0, $total - $discountAmount);

            // VAT Calculation (Assuming VAT is calculated on the final price after discount)
            $VAT = $taxablePrice * 0.1;
            $grandTotal = $taxablePrice + $VAT;

            // Create Payment Record
            $payment = Payment::create([
                'user_id'        => $user->id,
                'appointment_id' => $appointmentId,
                'order_id'       => $orderId,
                'invoice_code'   => $this->generateInvoiceCode(),
                'price'          => $taxablePrice, // Storing the Net Price after discount
                'VAT'            => $VAT,
                'total'          => $grandTotal,
                'created_by'     => $user->name,
                'payment_type'   => $paymentMethod,
            ]);

            // Save Promotion Usage
            if ($appliedPromotion) {
                PromotionUsage::create([
                    'promotion_id'   => $appliedPromotion->id,
                    'user_id'        => $user->id,
                    'appointment_id' => $appointmentId, // Link to the last created/processed appointment
                    'used_at'        => now(),
                ]);
            }

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
