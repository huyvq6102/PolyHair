<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\PromotionService;
use App\Models\Combo;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{

    public function checkout(){
        $cart = Session::get('cart', []);
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tiếp tục !');
        }

        $services = [];
        $subtotal = 0;
        $promotionAmount = 0; 

        foreach ($cart as $cartKey => $item) {

            // -------------------------
            // SERVICE VARIANT
            // -------------------------
            if (isset($item['type']) && $item['type'] === 'service_variant') {
                $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);

                if ($variant && $variant->service) {
                    $quantity = $item['quantity'] ?? 1;
                    $price = $variant->price * $quantity;

                    $services[] = [
                        'cart_id' => $cartKey,
                        'name' => $variant->service->name . ' - ' . $variant->name,
                        'price' => $price,
                        'type'  => 'service'
                    ];

                    $subtotal += $price;
                }
            }

            // -------------------------
            // COMBO
            // -------------------------
            if (isset($item['type']) && $item['type'] === 'combo') {
                $combo = Combo::find($item['id']);

                if ($combo) {
                    $quantity = $item['quantity'] ?? 1;
                    $price = $combo->price * $quantity;

                    $services[] = [
                        'cart_id' => $cartKey,
                        'name' => 'Combo: ' . $combo->name,
                        'price' => $price,
                        'type'  => 'combo'
                    ];

                    $subtotal += $price;
                }
            }

            // -------------------------
            // APPOINTMENT
            // -------------------------
            if (isset($item['type']) && $item['type'] === 'appointment') {
                $appointment = \App\Models\Appointment::with([
                    'appointmentDetails.serviceVariant.service',
                    'appointmentDetails.combo'
                ])->find($item['id']);

                if ($appointment) {
                    $appointmentTotal = 0;

                    foreach ($appointment->appointmentDetails as $detail) {
                        // Handle Service Variants in Appointment
                        if ($detail->serviceVariant && $detail->serviceVariant->service) {
                            $price = $detail->price_snapshot 
                                ?? ($detail->serviceVariant->price ?? 0);

                            $appointmentTotal += $price;

                            $services[] = [
                                'cart_id' => $cartKey,
                                'name' => '[Lịch hẹn] ' . $detail->serviceVariant->service->name . ' - ' . $detail->serviceVariant->name,
                                'price' => $price,
                                'type'  => 'appointment_item'
                            ];
                        }
                        // Handle Single Services (no variant) in Appointment
                        // These are stored with service_variant_id = null and service name in notes
                        elseif (!$detail->serviceVariant && !$detail->combo_id && $detail->notes) {
                            $price = $detail->price_snapshot ?? 0;

                            $appointmentTotal += $price;

                            $services[] = [
                                'cart_id' => $cartKey,
                                'name' => '[Lịch hẹn] ' . $detail->notes,
                                'price' => $price,
                                'type'  => 'appointment_item'
                            ];
                        }
                        // Handle Combos in Appointment
                        elseif ($detail->combo_id && $detail->combo) {
                            $price = $detail->price_snapshot ?? ($detail->combo->price ?? 0);

                            $appointmentTotal += $price;

                            $services[] = [
                                'cart_id' => $cartKey,
                                'name' => '[Lịch hẹn] Combo: ' . $detail->combo->name,
                                'price' => $price,
                                'type'  => 'appointment_item'
                            ];
                        }
                    }

                    $subtotal += $appointmentTotal;
                }
            }
        }

        // -------------------------
        // KHUYẾN MẠI (PROMOTION)
        // -------------------------
        $couponCode = Session::get('coupon_code');
        $appliedCoupon = null;
        $promotionMessage = null;

        if ($couponCode) {
            $promotionService = app(PromotionService::class);
            $result = $promotionService->validateAndCalculateDiscount(
                $couponCode,
                $cart,
                $subtotal,
                $user->id
            );

            if ($result['valid']) {
                $promotionAmount = $result['discount_amount'];
                $appliedCoupon = $result['promotion'];
                $promotionMessage = $result['message'];
            } else {
                // Mã không hợp lệ -> xóa khỏi session
                Session::forget('coupon_code');
                $promotionMessage = $result['message'];
            }
        }

        // -------------------------
        // TÍNH TỔNG (giống như PaymentService)
        // -------------------------
        $taxablePrice = max(0, $subtotal - $promotionAmount);
        
        // VAT Calculation (giống như PaymentService)
        $VAT = $taxablePrice * 0.1;
        $total = $taxablePrice + $VAT;

        return view('site.payments.show', [
            'customer' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ],
            'services' => $services,
            'promotion' => $promotionAmount,
            'appliedCoupon' => $appliedCoupon, // Để hiển thị mã đã dùng ở view
            'promotionMessage' => $promotionMessage, // Thông báo về promotion
            'subtotal' => $subtotal,
            'taxablePrice' => $taxablePrice, // Giá sau giảm giá (trước VAT)
            'vat' => $VAT, // VAT
            'total' => $total, // Tổng cuối cùng (sau VAT)
            'payment_methods' => [
                ['id' => 'card', 'name' => 'Thẻ tín dụng'],
                ['id' => 'vnpay', 'name' => 'VNPAY'],
                ['id' => 'zalopay', 'name' => 'ZaloPay'],
                ['id' => 'cash', 'name' => 'Thanh toán tại quầy'],
            ]
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50'
        ]);

        $code = $request->input('coupon_code');

        // Sử dụng PromotionService để validate promotion
        $promotionService = app(PromotionService::class);
        $result = $promotionService->validateAndCalculateDiscount($code, [], 0);
        
        if (!$result['valid']) {
            return back()->with('error', $result['message']);
        }
        
        $promo = $result['promotion'];

        Session::put('coupon_code', $code);

        return back()->with('success', 'Áp dụng mã khuyến mại thành công!');
    }

    public function removeCoupon()
    {
        Session::forget('coupon_code');
        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }

    public function processPayment(Request $request, PaymentService $paymentService, \App\Services\VnpayService $vnpayService){

        try {
            $cart = Session::get('cart', []);
            $user = auth()->user();

            \Log::info('Processing Payment Debug:', [
                'user_id' => $user ? $user->id : 'null',
                'cart_empty' => empty($cart),
                'cart_content' => $cart,
                'payment_method' => $request->input('payment_method')
            ]);

            if (!$user || empty($cart)) {
                return redirect()->route('site.payments.checkout')
                    ->with('error', 'Không thể thanh toán – giỏ hàng trống hoặc chưa đăng nhập.');
            }

            $couponCode = Session::get('coupon_code');
            $paymentMethod = $request->input('payment_method', 'cash');

            // Gọi Service để tạo đơn hàng (Status sẽ là Pending nếu là vnpay)
            $payment = $paymentService->processPayment($user, $cart, $paymentMethod, $couponCode);

            // Backup cart before clearing
            Session::put('cart_backup', $cart);

            Session::forget('cart');
            Session::forget('coupon_code');

            // -------------------------
            // XỬ LÝ VNPAY
            // -------------------------
            if ($paymentMethod === 'vnpay') {
                $vnpUrl = $vnpayService->createPayment($payment->invoice_code, $payment->total);
                return redirect($vnpUrl);
            }

            // Các phương thức khác (Tiền mặt, Credit Card giả định...)
            Session::forget('cart_backup'); // Success for non-vnpay
            return view('site.payments.success', [
                'appointmentId' => $payment->appointment_id,
                'invoiceCode'   => $payment->invoice_code,
                'total'         => $payment->total,
                'couponCode'    => $couponCode
            ]);

        } catch (\Exception $e) {
            \Log::error($e);
            return back()->with('error', 'Thanh toán thất bại: ' . $e->getMessage());
        }
    }

    public function vnpayReturn(Request $request, \App\Services\VnpayService $vnpayService)
    {
        $inputData = $request->all();
        
        if ($vnpayService->checkSignature($inputData)) {
            if (isset($inputData['vnp_ResponseCode']) && $inputData['vnp_ResponseCode'] == '00') {
                // Thanh toán THÀNH CÔNG
                Session::forget('cart_backup');
                $orderId = $inputData['vnp_TxnRef'];
                $payment = \App\Models\Payment::where('invoice_code', $orderId)->first();
                
                if ($payment) {
                    // Cập nhật trạng thái Payment
                    $payment->status = 'completed';
                    $payment->save();

                     // Cập nhật Appointment
                    if ($payment->appointment_id) {
                        $appointment = \App\Models\Appointment::find($payment->appointment_id);
                        if ($appointment) {
                            $appointment->status = 'Đã thanh toán';
                            $appointment->save();

                            // Cập nhật chi tiết
                            foreach ($appointment->appointmentDetails as $detail) {
                                $detail->status = 'Hoàn thành'; 
                                $detail->save();
                            }
                        }
                    }

                     // Cập nhật Order (nếu có)
                    if ($payment->order_id) {
                        $order = \App\Models\Order::find($payment->order_id);
                        if ($order) {
                            $order->status = 'Đã thanh toán';
                            $order->save();
                        }
                    }
                    
                    return view('site.payments.success', [
                        'appointmentId' => $payment->appointment_id,
                        'invoiceCode'   => $payment->invoice_code,
                        'total'         => $payment->total,
                        'couponCode'    => null 
                    ]);
                } else {
                     return redirect()->route('site.home')->with('error', 'Không tìm thấy đơn hàng.');
                }

            } else {
                // Thanh toán THẤT BẠI
                $orderId = $inputData['vnp_TxnRef'] ?? null;
                if ($orderId) {
                    $payment = \App\Models\Payment::where('invoice_code', $orderId)->first();
                    if ($payment) {
                        $payment->status = 'failed';
                        $payment->save();

                        if ($payment->appointment_id) {
                            $appointment = \App\Models\Appointment::find($payment->appointment_id);
                            if ($appointment) {
                                $appointment->status = 'Chưa thanh toán';
                                $appointment->save();
                            }
                        }
                    }
                }

                // Restore cart
                if (Session::has('cart_backup')) {
                    Session::put('cart', Session::get('cart_backup'));
                }

                return redirect()->route('site.payments.checkout')
                    ->with('error', 'Giao dịch không thành công hoặc bị hủy. Quý khách có thể thử lại.');
            }
        } else {
            return redirect()->route('site.payments.checkout')
                ->with('error', 'Chữ ký không hợp lệ. Giao dịch đáng ngờ.');
        }
    }
    
    public function paymentSuccess($appointmentId){
        return view('site.payments.success', compact('appointmentId'));
    }
}
