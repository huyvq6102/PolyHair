<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    /**
     * Display the cart page.
     */
    public function index()
    {
        try {
            $cart = Session::get('cart', []);
            
            // Remove duplicate appointments (same appointment ID but different keys)
            $seenAppointmentIds = [];
            $cleanedCart = [];
            foreach ($cart as $cartKey => $item) {
                if (isset($item['type']) && $item['type'] === 'appointment') {
                    $appointmentId = $item['id'] ?? null;
                    if ($appointmentId && !in_array($appointmentId, $seenAppointmentIds)) {
                        $seenAppointmentIds[] = $appointmentId;
                        $cleanedCart[$cartKey] = $item;
                    } elseif ($appointmentId && in_array($appointmentId, $seenAppointmentIds)) {
                        // Skip duplicate appointment
                        continue;
                    } else {
                        $cleanedCart[$cartKey] = $item;
                    }
                } else {
                    $cleanedCart[$cartKey] = $item;
                }
            }
            
            // Update cart if duplicates were removed
            if (count($cleanedCart) !== count($cart)) {
                Session::put('cart', $cleanedCart);
            }
            
            $cart = $cleanedCart;
            $total = 0;
            $items = [];
            $processedAppointmentIds = []; // Track processed appointment IDs to prevent duplicates

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
                        $appointmentId = $item['id'] ?? null;
                        
                        // Skip if this appointment ID has already been processed
                        if ($appointmentId && in_array($appointmentId, $processedAppointmentIds)) {
                            continue;
                        }
                        
                        $appointment = \App\Models\Appointment::with(['appointmentDetails.serviceVariant.service', 'appointmentDetails.combo', 'employee.user'])
                            ->find($appointmentId);
                        
                        if ($appointment) {
                            // Mark this appointment ID as processed
                            $processedAppointmentIds[] = $appointmentId;
                            $appointmentTotal = 0;
                            $serviceNames = [];
                            
                            foreach ($appointment->appointmentDetails as $detail) {
                                if ($detail->serviceVariant) {
                                    // Has variant - use variant info
                                    $appointmentTotal += $detail->price_snapshot ?? ($detail->serviceVariant->price ?? 0);
                                    $serviceNames[] = $detail->serviceVariant->name ?? 'Dịch vụ';
                                } elseif ($detail->combo_id && $detail->combo) {
                                    // Has combo - use combo info
                                    $appointmentTotal += $detail->price_snapshot ?? ($detail->combo->price ?? 0);
                                    $serviceNames[] = 'Combo: ' . ($detail->combo->name ?? 'Combo');
                                } else {
                                    // No variant/combo - use service info from notes and price_snapshot
                                    $appointmentTotal += $detail->price_snapshot ?? 0;
                                    $serviceName = $detail->notes ?? 'Dịch vụ đơn';
                                    $serviceNames[] = $serviceName;
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

            // Get user info if logged in
            $user = auth()->user();
            
            return view('site.cart.index', [
                'items' => $items ?? [],
                'total' => $total ?? 0,
                'user' => $user
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
            $item = $cart[$key];
            
            // If it's an appointment, delete from database permanently
            if (isset($item['type']) && $item['type'] === 'appointment' && isset($item['id'])) {
                try {
                    DB::beginTransaction();
                    
                    $appointment = \App\Models\Appointment::withTrashed()->find($item['id']);
                    
                    if ($appointment) {
                        // Delete appointment details first
                        \App\Models\AppointmentDetail::where('appointment_id', $appointment->id)->delete();
                        
                        // Delete appointment logs if exists
                        if (Schema::hasTable('appointment_logs')) {
                            \App\Models\AppointmentLog::where('appointment_id', $appointment->id)->delete();
                        }
                        
                        // Delete promotion usages if exists
                        if (Schema::hasTable('promotion_usages')) {
                            \App\Models\PromotionUsage::where('appointment_id', $appointment->id)->delete();
                        }
                        
                        // Delete reviews if exists
                        if (Schema::hasTable('reviews')) {
                            \App\Models\Review::where('appointment_id', $appointment->id)->delete();
                        }
                        
                        // Delete payments if exists
                        if (Schema::hasTable('payments')) {
                            \App\Models\Payment::where('appointment_id', $appointment->id)->delete();
                        }
                        
                        // Force delete the appointment (permanent delete from database)
                        $appointment->forceDelete();
                    }
                    
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    \Log::error('Error deleting appointment from cart: ' . $e->getMessage());
                    // Continue to remove from cart even if database delete fails
                }
            }
            
            // Remove from cart session
            unset($cart[$key]);
            Session::put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa khỏi lịch đặt' . (isset($item['type']) && $item['type'] === 'appointment' ? ' và xóa khỏi hệ thống' : ''),
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
