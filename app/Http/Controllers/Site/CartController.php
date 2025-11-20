<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the cart page.
     */
    public function index()
    {
        try {
            $cart = Session::get('cart', []);
            $total = 0;
            $items = [];

            foreach ($cart as $cartKey => $item) {
                try {
                    if (isset($item['type']) && $item['type'] === 'service_variant') {
                        $variant = \App\Models\ServiceVariant::with('service')->find($item['id']);
                        if ($variant) {
                            $price = $variant->price ?? 0;
                            $subtotal = $price * ($item['quantity'] ?? 1);
                            $total += $subtotal;
                            
                            $items[] = [
                                'key' => $cartKey,
                                'id' => $item['id'],
                                'type' => 'service_variant',
                                'name' => $variant->name ?? 'Dịch vụ',
                                'service_name' => $variant->service->name ?? 'Dịch vụ',
                                'price' => $price,
                                'quantity' => $item['quantity'] ?? 1,
                                'subtotal' => $subtotal,
                                'duration' => $variant->duration ?? 60,
                                'variant' => $variant,
                            ];
                        }
                    } elseif (isset($item['type']) && $item['type'] === 'appointment') {
                        // Appointment items (from booking)
                        $appointment = \App\Models\Appointment::with(['appointmentDetails.serviceVariant.service', 'employee.user'])
                            ->find($item['id']);
                        
                        if ($appointment) {
                            $appointmentTotal = 0;
                            $serviceNames = [];
                            
                            foreach ($appointment->appointmentDetails as $detail) {
                                if ($detail->serviceVariant) {
                                    $appointmentTotal += $detail->price_snapshot ?? ($detail->serviceVariant->price ?? 0);
                                    $serviceNames[] = $detail->serviceVariant->name ?? 'Dịch vụ';
                                }
                            }
                            
                            $total += $appointmentTotal;
                            
                            $items[] = [
                                'key' => $cartKey,
                                'id' => $item['id'],
                                'type' => 'appointment',
                                'name' => 'Đặt lịch #' . str_pad($appointment->id, 6, '0', STR_PAD_LEFT),
                                'services' => !empty($serviceNames) ? implode(', ', $serviceNames) : 'N/A',
                                'price' => $appointmentTotal,
                                'quantity' => 1,
                                'subtotal' => $appointmentTotal,
                                'appointment_date' => $appointment->start_at ? $appointment->start_at->format('d/m/Y') : 'N/A',
                                'appointment_time' => $appointment->start_at ? $appointment->start_at->format('H:i') : 'N/A',
                                'employee_name' => ($appointment->employee && $appointment->employee->user) 
                                    ? $appointment->employee->user->name 
                                    : 'Chưa xác định',
                                'status' => $appointment->status ?? 'Chờ xử lý',
                                'appointment' => $appointment,
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    // Skip invalid items
                    continue;
                }
            }

            return view('site.cart.index', [
                'items' => $items ?? [],
                'total' => $total ?? 0
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Cart index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty cart on error
            return view('site.cart.index', [
                'items' => [],
                'total' => 0
            ]);
        }
    }

    /**
     * Add item to cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'type' => 'required|in:service_variant,appointment',
            'id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $cart = Session::get('cart', []);
        $key = $request->type . '_' . $request->id;

        if ($request->type === 'service_variant') {
            if (isset($cart[$key])) {
                $cart[$key]['quantity'] += $request->quantity ?? 1;
            } else {
                $cart[$key] = [
                    'type' => 'service_variant',
                    'id' => $request->id,
                    'quantity' => $request->quantity ?? 1,
                ];
            }
        } elseif ($request->type === 'appointment') {
            // Don't allow duplicate appointments
            if (!isset($cart[$key])) {
                $cart[$key] = [
                    'type' => 'appointment',
                    'id' => $request->id,
                    'quantity' => 1,
                ];
            }
        }

        Session::put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào lịch đặt',
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request, $key)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Session::get('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $request->quantity;
            Session::put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật lịch đặt',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm',
        ], 404);
    }

    /**
     * Remove item from cart.
     */
    public function remove($key)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            Session::put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi lịch đặt',
                'cart_count' => count($cart),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sản phẩm',
        ], 404);
    }

    /**
     * Clear cart.
     */
    public function clear()
    {
        Session::forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ lịch đặt',
        ]);
    }

    /**
     * Get cart count (for AJAX).
     */
    public function count()
    {
        $cart = Session::get('cart', []);
        return response()->json(['count' => count($cart)]);
    }

    /**
     * Add fake data to cart for testing.
     */
    public function seedFakeData()
    {
        $cart = [];
        
        // Add service variants to cart
        $serviceVariants = \App\Models\ServiceVariant::with('service')
            ->where('is_active', true)
            ->limit(3)
            ->get();
        
        foreach ($serviceVariants as $index => $variant) {
            $key = 'service_variant_' . $variant->id;
            $cart[$key] = [
                'type' => 'service_variant',
                'id' => $variant->id,
                'quantity' => $index + 1, // Different quantities for testing
            ];
        }
        
        // Add appointments to cart if they exist
        $appointments = \App\Models\Appointment::with(['appointmentDetails.serviceVariant.service', 'employee.user'])
            ->limit(2)
            ->get();
        
        foreach ($appointments as $appointment) {
            $key = 'appointment_' . $appointment->id;
            $cart[$key] = [
                'type' => 'appointment',
                'id' => $appointment->id,
                'quantity' => 1,
            ];
        }
        
        // If no appointments exist, create a fake one
        if ($appointments->isEmpty()) {
            $user = \App\Models\User::first();
            $employee = \App\Models\Employee::first();
            $serviceVariant = \App\Models\ServiceVariant::first();
            
            if ($user && $serviceVariant) {
                $appointment = \App\Models\Appointment::create([
                    'user_id' => $user->id,
                    'employee_id' => $employee ? $employee->id : null,
                    'status' => 'Chờ xử lý',
                    'start_at' => now()->addDays(1)->setTime(10, 0),
                    'end_at' => now()->addDays(1)->setTime(11, 0),
                    'note' => 'Đặt lịch test',
                ]);
                
                \App\Models\AppointmentDetail::create([
                    'appointment_id' => $appointment->id,
                    'service_variant_id' => $serviceVariant->id,
                    'employee_id' => $employee ? $employee->id : null,
                    'price_snapshot' => $serviceVariant->price,
                    'duration' => $serviceVariant->duration ?? 60,
                    'status' => 'Chờ',
                ]);
                
                $key = 'appointment_' . $appointment->id;
                $cart[$key] = [
                    'type' => 'appointment',
                    'id' => $appointment->id,
                    'quantity' => 1,
                ];
            }
        }
        
        Session::put('cart', $cart);
        
        return redirect()->route('site.cart.index')
            ->with('success', 'Đã thêm dữ liệu mẫu vào lịch đặt!');
    }
}
