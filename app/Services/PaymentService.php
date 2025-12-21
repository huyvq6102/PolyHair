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
use App\Services\PromotionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Process payment for the given cart and user.
     *
     * @param \App\Models\User|null $user
     * @param array $cart
     * @param string $paymentMethod
     * @param string|null $couponCode
     * @return \App\Models\Payment
     * @throws Exception
     */
    public function processPayment($user = null, $cart, $paymentMethod = 'cash', $couponCode = null)
    {
        DB::beginTransaction();

        try {
            $total = 0;
            $appointmentId = null;
            $orderId = null;
            $orderItems = [];
            $userId = $user ? $user->id : null;
            $userName = $user ? $user->name : 'Khách vãng lai';
            $userPhone = $user ? $user->phone : '';
            $userAddress = $user ? ($user->address ?? 'Tại cửa hàng') : 'Tại cửa hàng';

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
                    // Nếu thanh toán tại quầy hoặc Online, status = 'Chờ xử lý'
                    $appointmentStatus = in_array($paymentMethod, ['cash', 'momo', 'vnpay']) ? 'Chờ xử lý' : 'Đã thanh toán';
                    $appointment = Appointment::create([
                        'user_id'    => $userId,
                        'status'     => $appointmentStatus,
                        'start_at'   => now(),
                        'end_at'     => now()->addMinutes($variant->duration),
                    ]);

                    $appointmentId = $appointment->id;

                    // Status của detail phụ thuộc vào payment method
                    $detailStatus = ($paymentMethod === 'cash') ? 'Chờ' : 'Hoàn thành';
                    AppointmentDetail::create([
                        'appointment_id'      => $appointment->id,
                        'service_variant_id'  => $variant->id,
                        'price_snapshot'      => $variant->price,
                        'duration'            => $variant->duration,
                        'status'              => $detailStatus,
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
                        // Nếu thanh toán tại quầy hoặc Online, status = 'Chờ xử lý'
                        $appointmentStatus = in_array($paymentMethod, ['cash', 'momo', 'vnpay']) ? 'Chờ xử lý' : 'Đã thanh toán';
                        $appointment = Appointment::create([
                            'user_id'    => $userId,
                            'status'     => $appointmentStatus,
                            'start_at'   => now(),
                            'end_at'     => now()->addMinutes(60), 
                        ]);

                        $appointmentId = $appointment->id;

                        // Status của detail phụ thuộc vào payment method
                        $detailStatus = ($paymentMethod === 'cash') ? 'Chờ' : 'Hoàn thành';
                        AppointmentDetail::create([
                            'appointment_id'      => $appointment->id,
                            'combo_id'            => $combo->id,
                            'price_snapshot'      => $combo->price,
                            'duration'            => 60, // Default
                            'status'              => $detailStatus,
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

                        // Nếu thanh toán tại quầy hoặc Online, status = 'Chờ xử lý'
                        $appointmentStatus = in_array($paymentMethod, ['cash', 'momo', 'vnpay']) ? 'Chờ xử lý' : 'Đã thanh toán';
                        $appointment->status = $appointmentStatus;
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
                    'id_user' => $userId, // Can be null now
                    'status' => 'Đã thanh toán',
                    'address' => $userAddress, 
                    'phone' => $userPhone,
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
                $promotionService = app(PromotionService::class);
                $result = $promotionService->validateAndCalculateDiscount(
                    $couponCode,
                    $cart,
                    $total,
                    $userId
                );

                if ($result['valid']) {
                    $discountAmount = $result['discount_amount'];
                    $appliedPromotion = $result['promotion'];
                }
                // Nếu không hợp lệ, vẫn tiếp tục thanh toán nhưng không có giảm giá
            }

            $taxablePrice = max(0, $total - $discountAmount);

            // VAT Calculation (Assuming VAT is calculated on the final price after discount)
            // $VAT = $taxablePrice * 0.1;
            $VAT = 0;
            $grandTotal = $taxablePrice; // + $VAT;

            // Check for existing pending payment for this appointment
            $existingPayment = null;
            if ($appointmentId) {
                $existingPayment = Payment::where('appointment_id', $appointmentId)
                    ->where('status', 'pending')
                    ->first();
            }

            if ($existingPayment) {
                // Update existing pending payment
                $existingPayment->update([
                    'user_id'        => $userId,
                    'order_id'       => $orderId,
                    'invoice_code'   => $this->generateInvoiceCode(), // Regenerate for fresh gateway ref
                    'price'          => $taxablePrice,
                    'total'          => $grandTotal,
                    'created_by'     => $userName,
                    'payment_type'   => $paymentMethod,
                    'status'         => 'pending',
                ]);
                $payment = $existingPayment;
            } else {
                // Create Payment Record
                // Nếu thanh toán tại quầy, vẫn tạo payment record nhưng có thể đánh dấu là chưa thanh toán
                // Hoặc có thể không tạo payment record cho đến khi thanh toán thực sự
                // Ở đây tôi sẽ tạo payment record để theo dõi, nhưng status của appointment sẽ là "Chờ xử lý"
                $payment = Payment::create([
                    'user_id'        => $userId,
                    'appointment_id' => $appointmentId,
                    'order_id'       => $orderId,
                    'invoice_code'   => $this->generateInvoiceCode(),
                    'price'          => $taxablePrice, // Storing the Net Price after discount
                    // 'VAT'            => $VAT,
                    'total'          => $grandTotal,
                    'created_by'     => $userName,
                    'payment_type'   => $paymentMethod,
                    'status'         => 'pending',
                ]);
            }

            // Save Promotion Usage (chỉ lưu nếu có promotion được áp dụng và có user)
            // Chỉ lưu khi appointment status là "Đã thanh toán" hoặc payment method không phải cash/momo/vnpay
            if ($appliedPromotion && $appointmentId && $userId) {
                // Kiểm tra xem đã có PromotionUsage cho appointment này chưa
                $existingUsage = PromotionUsage::where('appointment_id', $appointmentId)
                    ->where('promotion_id', $appliedPromotion->id)
                    ->where('user_id', $userId)
                    ->first();
                
                if (!$existingUsage) {
                    // Chỉ tạo PromotionUsage nếu appointment đã thanh toán hoặc payment method là online
                    $appointment = Appointment::find($appointmentId);
                    if ($appointment && ($appointment->status === 'Đã thanh toán' || !in_array($paymentMethod, ['cash', 'momo', 'vnpay']))) {
                        PromotionUsage::create([
                            'promotion_id'   => $appliedPromotion->id,
                            'user_id'        => $userId,
                            'appointment_id' => $appointmentId,
                            'used_at'        => now(),
                        ]);

                        // Giảm số lượt dùng còn lại (usage_limit) nếu đang được giới hạn
                        if (!is_null($appliedPromotion->usage_limit) && $appliedPromotion->usage_limit > 0) {
                            $appliedPromotion->decrement('usage_limit', 1);
                            $appliedPromotion->refresh();
                        }
                    }
                }
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
