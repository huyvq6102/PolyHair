<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
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
                $appointment = \App\Models\Appointment::with('appointmentDetails.serviceVariant.service')
                    ->find($item['id']);

                if ($appointment) {
                    $appointmentTotal = 0;

                    foreach ($appointment->appointmentDetails as $detail) {
                        // Handle Standard Services in Appointment
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
                        // Handle Combos in Appointment (if stored directly or via logic)
                        // Note: Current AppointmentDetail logic primarily links to serviceVariant. 
                        // If combos are broken down into variants, they are handled above.
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

        if ($couponCode) {
            $promo = Promotion::where('code', $couponCode)
                ->where('status', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first();

            if ($promo) {
                // Tính giảm giá theo phần trăm
                $promotionAmount = $subtotal * ($promo->discount_percent / 100);
                $appliedCoupon = $promo;
            } else {
                // Mã không hợp lệ hoặc hết hạn -> xóa khỏi session
                Session::forget('coupon_code');
            }
        }

        // -------------------------
        // TÍNH TỔNG
        // -------------------------
        $total = max(0, $subtotal - $promotionAmount);

        return view('site.payments.show', [
            'customer' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ],
            'services' => $services,
            'promotion' => $promotionAmount,
            'appliedCoupon' => $appliedCoupon, // Để hiển thị mã đã dùng ở view
            'subtotal' => $subtotal,
            'total' => $total,
            'payment_methods' => [
                ['id' => 'card', 'name' => 'Thẻ tín dụng'],
                ['id' => 'momo', 'name' => 'Ví MoMo'],
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

        $promo = Promotion::where('code', $code)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$promo) {
            return back()->with('error', 'Mã khuyến mại không hợp lệ hoặc đã hết hạn.');
        }

        Session::put('coupon_code', $code);

        return back()->with('success', 'Áp dụng mã khuyến mại thành công!');
    }

    public function removeCoupon()
    {
        Session::forget('coupon_code');
        return back()->with('success', 'Đã gỡ bỏ mã khuyến mại.');
    }

    public function processPayment(Request $request, PaymentService $paymentService){

        try {
            $cart = Session::get('cart', []);
            $user = auth()->user();

            if (!$user || empty($cart)) {
                return redirect()->route('site.payments.checkout')
                    ->with('error', 'Không thể thanh toán – giỏ hàng trống hoặc chưa đăng nhập.');
            }

            // Inject coupon code into request for the Service if needed, 
            // or rely on the Service checking the Session/Database logic.
            // Here we pass the coupon code explicitly if the service method signature allows,
            // but since we can't see the service, we rely on standard flow. 
            // Assuming PaymentService handles the final calculation or we pass the pre-calculated values.
            
            // Note: Ideally, PaymentService should recalculate totals to prevent frontend manipulation.
            // We will attach the coupon code to the payload if the service supports it.
            $couponCode = Session::get('coupon_code');

            // Gọi Service để xử lý thanh toán
            // Update: Passing coupon_code context if possible, otherwise Service must handle session('coupon_code')
            $payment = $paymentService->processPayment($user, $cart, $request->input('payment_method', 'cash'), $couponCode);

            Session::forget('cart');
            Session::forget('coupon_code'); // Clear coupon after use

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
    
    public function paymentSuccess($appointmentId){
        return view('site.payments.success', compact('appointmentId'));
    }
}
