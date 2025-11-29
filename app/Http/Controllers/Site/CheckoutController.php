<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
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

                if ($variant && $variant->service) {
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
                        if (!$detail->serviceVariant || !$detail->serviceVariant->service) {
                            continue; // Bỏ qua nếu serviceVariant hoặc service không tồn tại
                        }

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
                    
                    if (!$variant || !$variant->service) {
                        continue; // Bỏ qua nếu variant hoặc service không tồn tại
                    }
                    
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
                        if (!$detail->serviceVariant || !$detail->serviceVariant->service) {
                            continue; // Bỏ qua nếu serviceVariant hoặc service không tồn tại
                        }

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
