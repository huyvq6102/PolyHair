<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    /**
     * Display the cart page.
     */
    // public function checkout(Request $request){  
    //     DB::beginTransaction();
    //     try {
    //         $cart = Session::get('cart', []);

    //         if (empty($cart)) {
    //             return redirect()->back()->with('error', 'Giỏ hàng trống.');
    //         }

    //         $user = auth()->user();
    //         if (!$user) {
    //             return redirect()->route('site.login')->with('error', 'Bạn cần đăng nhập để thanh toán.');
    //         }

    //         $total = 0;
    //         $paymentItems = [];

    //         foreach ($cart as $cartKey => $item) {

    //             // -----------------------------
    //             // 1. ITEM LÀ SERVICE VARIANT
    //             // -----------------------------
    //             if (isset($item['type']) && $item['type'] === 'service_variant') {
    //                 $variant = \App\Models\ServiceVariant::find($item['id']);
    //                 if (!$variant) continue;

    //                 $quantity = $item['quantity'] ?? 1;
    //                 $subtotal = $variant->price * $quantity;
    //                 $total += $subtotal;

    //                 $paymentItems[] = [
    //                     'type' => 'service_variant',
    //                     'id' => $variant->id,
    //                     'name' => $variant->name,
    //                     'price' => $variant->price,
    //                     'quantity' => $quantity,
    //                     'subtotal' => $subtotal
    //                 ];
    //             }

    //             // -----------------------------
    //             // 2. ITEM LÀ APPOINTMENT
    //             // -----------------------------
    //             if (isset($item['type']) && $item['type'] === 'appointment') {
    //                 $appointment = \App\Models\Appointment::with('appointmentDetails.serviceVariant')
    //                     ->find($item['id']);

    //                 if (!$appointment) continue;

    //                 $appointmentTotal = 0;

    //                 foreach ($appointment->appointmentDetails as $detail) {
    //                     $price = $detail->price_snapshot 
    //                         ?? ($detail->serviceVariant->price ?? 0);
    //                     $appointmentTotal += $price;
    //                 }

    //                 $total += $appointmentTotal;

    //                 // Lưu item chi tiết thanh toán
    //                 $paymentItems[] = [
    //                     'type' => 'appointment',
    //                     'id' => $appointment->id,
    //                     'price' => $appointmentTotal,
    //                     'subtotal' => $appointmentTotal,
    //                     'services' => $appointment->appointmentDetails->pluck('serviceVariant.name')->implode(', ')
    //                 ];

    //                 // Cập nhật trạng thái cuộc hẹn → đã thanh toán
    //                 $appointment->status = 'Đã thanh toán';
    //                 $appointment->save();

    //                 // LOG trạng thái
    //                 \App\Models\AppointmentLog::create([
    //                     'appointment_id' => $appointment->id,
    //                     'status_from' => $appointment->status,
    //                     'status_to' => 'Đã thanh toán',
    //                     'modified_by' => $user->id
    //                 ]);
    //             }
    //         }

    //         // -------------------------------------
    //         //  TÍNH VAT VÀ TOTAL
    //         // -------------------------------------
    //         $VAT = $total * 0.1;   // VAT 10%
    //         $grandTotal = $total + $VAT;

    //         // -------------------------------------
    //         // 3. TẠO PAYMENT RECORD
    //         // -------------------------------------
    //         $payment = \App\Models\Payment::create([
    //             'user_id' => $user->id,
    //             'appointment_id' => null, // nếu là appointment đơn, bạn gán thêm
    //             'price' => $total,
    //             'VAT' => $VAT,
    //             'total' => $grandTotal,
    //             'created_by' => $user->name,
    //             'payment_type' => 'cash'   // hoặc 'online'
    //         ]);

    //         DB::commit();

    //         // -------------------------------------
    //         // 4. XOÁ GIỎ HÀNG
    //         // -------------------------------------
    //         Session::forget('cart');

    //         return view('site.cart.success', [
    //             'payment' => $payment,
    //             'items' => $paymentItems,
    //             'total' => $grandTotal
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         \Log::error('Checkout error: '.$e->getMessage());

    //         return redirect()->back()->with('error', 'Thanh toán thất bại, vui lòng thử lại.');
    //     }
    // }

    public function checkout(){
        $cart = Session::get('cart', []);
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để tiếp tục');
        }

        $services = [];
        $subtotal = 0;
        $promotion = 0; // ví dụ, sau này gắn với bảng promotions

        foreach ($cart as $cartKey => $item) {

            // -------------------------
            // SERVICE VARIANT
            // -------------------------
            if ($item['type'] === 'service_variant') {
                $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);

                if ($variant) {
                    $quantity = $item['quantity'] ?? 1;
                    $price = $variant->price * $quantity;

                    $services[] = [
                        'cart_id' => $cartKey,
                        'name' => $variant->service->name . ' - ' . $variant->name,
                        'price' => $price
                    ];

                    $subtotal += $price;
                }
            }

            // -------------------------
            // APPOINTMENT
            // -------------------------
            if ($item['type'] === 'appointment') {
                $appointment = \App\Models\Appointment::with('appointmentDetails.serviceVariant.service')
                    ->find($item['id']);

                if ($appointment) {
                    $appointmentTotal = 0;

                    foreach ($appointment->appointmentDetails as $detail) {
                        $price = $detail->price_snapshot 
                            ?? ($detail->serviceVariant->price ?? 0);

                        $appointmentTotal += $price;

                        $services[] = [
                            'cart_id' => $cartKey,
                            'name' => $detail->serviceVariant->service->name . ' - ' . $detail->serviceVariant->name,
                            'price' => $price
                        ];
                    }

                    $subtotal += $appointmentTotal;
                }
            }
        }

        // -------------------------
        // TÍNH TỔNG
        // -------------------------
        $total = $subtotal + $promotion;

        return view('site.payments.show', [
            'customer' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email
            ],
            'services' => $services,
            'promotion' => $promotion,
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


    public function processPayment(Request $request){

        
        DB::beginTransaction();

        try {
            $cart = Session::get('cart', []);
            $user = auth()->user();

            if (!$user || empty($cart)) {
                return redirect()->route('site.payments.checkout')
                    ->with('error', 'Không thể thanh toán – giỏ hàng trống hoặc chưa đăng nhập.');
            }

            $total = 0;
            $appointmentId = null;
            $items = [];

            foreach ($cart as $key => $item) {

                // SERVICE VARIANT
                if ($item['type'] === 'service_variant') {
                    $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                    $price = $variant->price * ($item['quantity'] ?? 1);

                    $appointment = \App\Models\Appointment::create([
                        'user_id'    => $user->id,
                        'status'     => 'Đã thanh toán',
                        'start_at'   => now(),
                        'end_at'     => now()->addMinutes($variant->duration),
                    ]);

                    $appointmentId = $appointment->id;

                    \App\Models\AppointmentDetail::create([
                        'appointment_id'      => $appointment->id,
                        'service_variant_id'  => $variant->id,
                        'price_snapshot'      => $variant->price,
                        'duration'            => $variant->duration,
                        'status'              => 'Hoàn thành',
                    ]);

                    $total += $price;

                    $items[] = [
                        'name' => $variant->service->name . ' - ' . $variant->name,
                        'price' => $price,
                    ];
                }

                // APPOINTMENT
                if ($item['type'] === 'appointment') {
                    $appointment = \App\Models\Appointment::with('appointmentDetails.serviceVariant')
                        ->find($item['id']);

                    $appointmentId = $appointment->id;
                    $appointmentTotal = 0;

                    foreach ($appointment->appointmentDetails as $detail) {
                        $price = $detail->price_snapshot ?? $detail->serviceVariant->price;
                        $appointmentTotal += $price;

                        $items[] = [
                            'name' => $detail->serviceVariant->service->name . ' - ' . $detail->serviceVariant->name,
                            'price' => $price,
                        ];
                    }

                    $total += $appointmentTotal;

                    $appointment->status = 'Đã thanh toán';
                    $appointment->save();
                }
            }

            // VAT
            $VAT = $total * 0.1;
            $grandTotal = $total + $VAT;

            // CREATE PAYMENT
            $payment = \App\Models\Payment::create([
                'user_id'        => $user->id,
                'appointment_id' => $appointmentId,
                'price'          => $total,
                'VAT'            => $VAT,
                'total'          => $grandTotal,
                'created_by'     => $user->name,
                'payment_type'   => 'cash',
            ]);

            DB::commit();

            Session::forget('cart');

            return view('site.payments.success', compact('appointmentId'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return back()->with('error', 'Thanh toán thất bại, vui lòng thử lại.');
        }
    }
    

    public function paymentSuccess($appointmentId){
        return view('site.payments.success', compact('appointmentId'));
    }





}
