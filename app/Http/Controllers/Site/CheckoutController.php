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

    private function getCartData()
    {
        $cart = Session::get('cart', []);
        $services = [];
        $subtotal = 0;

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
                        'type'  => 'service',
                        // Add raw data for promotion service
                        'raw_item' => $item
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
                        'type'  => 'combo',
                        'raw_item' => $item
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
                    // Appointment itself is the item for promotion calculation logic if passed directly, 
                    // but logic uses individual items inside.
                    // Actually PromotionService iterates over the cart items passed to it.
                    // So we need to pass the cart items, not the flattened services array for display.
                    // But we calculate subtotal here.
                    
                    $subtotal += $appointmentTotal;
                }
            }
        }
        
        return [$cart, $services, $subtotal];
    }

    public function checkout(Request $request){
        $user = auth()->user();

        // Check if appointment_id is passed (e.g. from Admin or Link)
        if ($request->has('appointment_id')) {
            $appointmentId = $request->input('appointment_id');
            $appointment = \App\Models\Appointment::find($appointmentId);

            if ($appointment) {
                if ($appointment->status === 'Đã thanh toán') {
                    // Redirect to appointment details page with a message
                    return redirect()->route('admin.appointments.show', $appointment->id)
                                     ->with('info', 'Lịch hẹn này đã được thanh toán.');
                }
                // Set cart to this appointment only
                Session::put('cart', [
                    'appointment_' . $appointment->id => [
                        'type' => 'appointment',
                        'id' => $appointment->id,
                        'quantity' => 1
                    ]
                ]);
                // If user is not logged in, we might still want to proceed if we have a valid appointment?
                // For now, we'll rely on the logic below.
            }
        }

        list($cart, $services, $subtotal) = $this->getCartData();

        // Determine Customer Info
        $customerData = null;
        
        // Priority: Appointment User
        foreach ($cart as $item) {
            if (isset($item['type']) && $item['type'] === 'appointment') {
                $appt = \App\Models\Appointment::with('user')->find($item['id']);
                if ($appt && $appt->user) {
                    $customerData = [
                        'name' => $appt->user->name,
                        'phone' => $appt->user->phone,
                        'email' => $appt->user->email
                    ];
                }
            }
        }

        // Fallback: Logged in User
        if (!$customerData && $user) {
            $customerData = [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ];
        }

        // If no user logged in AND no appointment user found -> Redirect Login
        if (!$user && !$customerData) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tiếp tục !');
        }

        $promotionAmount = 0; 

        // -------------------------
        // KHUYẾN MẠI (PROMOTION)
        // -------------------------
        $couponCode = Session::get('coupon_code');
        $appliedCoupon = null;
        $promotionMessage = null;

        if ($couponCode) {
            $promotionService = app(PromotionService::class);
            // Use the determined user ID for promotion validation if available, else logged in user
            $userIdForPromo = $user ? $user->id : null;
            
            // If we have an appointment user, maybe we should use THAT ID?
            // But strict promotion rules might require the logged-in account to own the promotion?
            // Let's stick to logged-in user for now, or if admin is paying, maybe skipping user check?
            // PromotionService uses user_id to check usage limits.
            
            $result = $promotionService->validateAndCalculateDiscount(
                $couponCode,
                $cart,
                $subtotal,
                $userIdForPromo
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
        // $VAT = $taxablePrice * 0.1;
        $VAT = 0;
        $total = $taxablePrice; // + $VAT;

        return view('admin.payments.checkout', [
            'customer' => $customerData,
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
        $user = auth()->user();

        // Calculate Cart Data
        list($cart, $services, $subtotal) = $this->getCartData();

        // Sử dụng PromotionService để validate promotion
        $promotionService = app(PromotionService::class);
        $result = $promotionService->validateAndCalculateDiscount(
            $code, 
            $cart, 
            $subtotal,
            $user ? $user->id : null
        );
        
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
            
            // Determine the "Payer" user (Customer)
            $payer = $user;
            
            // Check if cart has appointment and use that user if available
            foreach ($cart as $item) {
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    $appt = \App\Models\Appointment::find($item['id']);
                    if ($appt && $appt->user) {
                        $payer = $appt->user;
                        break; 
                    }
                }
            }

            if (!$payer || empty($cart)) {
                return redirect()->route('site.payments.checkout')
                    ->with('error', 'Không thể thanh toán – giỏ hàng trống hoặc chưa đăng nhập.');
            }

            $couponCode = Session::get('coupon_code');
            $paymentMethod = $request->input('payment_method', 'cash');

            // Gọi Service để tạo đơn hàng (Status sẽ là Pending nếu là vnpay)
            $payment = $paymentService->processPayment($payer, $cart, $paymentMethod, $couponCode);

            // -------------------------
            // AUTO-COMPLETE CASH PAYMENT FOR ADMIN/STAFF
            // -------------------------
            if ($paymentMethod === 'cash') {
                $authUser = auth()->user();
                // If the person performing the action is Admin or Staff
                // Or if we decide that "Cash" in this context always means "Paid" (Simpler for now?)
                // Let's stick to Admin/Staff check to be safe.
                if ($authUser && ($authUser->isAdmin() || $authUser->isEmployee())) {
                     // Mark payment as completed
                     $payment->status = 'completed';
                     $payment->save();
                     
                     // Mark appointment as Paid
                     if ($payment->appointment_id) {
                         $appt = \App\Models\Appointment::find($payment->appointment_id);
                         if ($appt) {
                             $appt->status = 'Đã thanh toán';
                             $appt->save();
                             
                             // Ghi nhận việc sử dụng khuyến mãi TRƯỚC KHI xóa session
                             $appt->recordPromotionUsage();
                             
                             // Update details status
                             foreach ($appt->appointmentDetails as $detail) {
                                $detail->status = 'Hoàn thành';
                                $detail->save();
                             }
                         }
                     }
                     
                     // Mark order as Paid
                     if ($payment->order_id) {
                         $order = \App\Models\Order::find($payment->order_id);
                         if ($order) {
                             $order->status = 'Đã thanh toán';
                             $order->save();
                         }
                     }
                }
            }

            // Backup cart before clearing
            Session::put('cart_backup', $cart);

            // Xóa session SAU KHI đã ghi nhận promotion usage
            Session::forget('cart');
            Session::forget('coupon_code');
            Session::forget('applied_promotion_id');

            // -------------------------
            // XỬ LÝ VNPAY
            // -------------------------
            if ($paymentMethod === 'vnpay') {
                $vnpUrl = $vnpayService->createPayment($payment->invoice_code, $payment->total);
                return redirect($vnpUrl);
            }

            // Các phương thức khác (Tiền mặt, Credit Card giả định...)
            Session::forget('cart_backup'); // Success for non-vnpay
            return view('admin.payments.success', [
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

                            // Ghi nhận việc sử dụng khuyến mãi
                            $appointment->recordPromotionUsage();

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
                    
                    // CHECK SOURCE FOR REDIRECT
                    $source = Session::get('payment_source');
                    if ($source === 'employee') {
                        Session::forget('payment_source');
                        Session::forget('payment_appointment_id');
                        return redirect()->route('employee.appointments.index')
                            ->with('success', 'Thanh toán thành công (VNPAY)!');
                    } elseif ($source === 'admin') {
                        Session::forget('payment_source');
                        Session::forget('payment_appointment_id');
                        return redirect()->route('admin.appointments.index')
                            ->with('success', 'Thanh toán thành công (VNPAY)!');
                    }
                    
                    return view('admin.payments.success', [
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
                                $appointment->status = 'Chưa thanh toán'; // Revert status
                                $appointment->save();
                            }
                        }
                    }
                }

                // Restore cart
                if (Session::has('cart_backup')) {
                    Session::put('cart', Session::get('cart_backup'));
                }

                // CHECK SOURCE FOR REDIRECT
                $source = Session::get('payment_source');
                if ($source === 'employee') {
                    $apptId = Session::get('payment_appointment_id');
                    Session::forget('payment_source');
                    Session::forget('payment_appointment_id');
                    
                    if ($apptId) {
                        return redirect()->route('employee.appointments.checkout', ['appointment_id' => $apptId])
                           ->with('error', 'Giao dịch VNPAY không thành công hoặc bị hủy.');
                    }
                    return redirect()->route('employee.appointments.index')
                        ->with('error', 'Giao dịch VNPAY không thành công.');
                } elseif ($source === 'admin') {
                    $apptId = Session::get('payment_appointment_id');
                    Session::forget('payment_source');
                    Session::forget('payment_appointment_id');
                    
                    if ($apptId) {
                        return redirect()->route('admin.appointments.checkout', ['appointment_id' => $apptId])
                           ->with('error', 'Giao dịch VNPAY không thành công hoặc bị hủy.');
                    }
                    return redirect()->route('admin.appointments.index')
                        ->with('error', 'Giao dịch VNPAY không thành công.');
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
        return view('admin.payments.success', compact('appointmentId'));
    }
}
