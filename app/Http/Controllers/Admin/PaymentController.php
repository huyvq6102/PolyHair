<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     */
    public function index()
    {
        $payments = Payment::with(['user', 'appointment', 'order'])
            ->latest()
            ->paginate(10);

        return view('admin.payments.index', compact('payments'));
    }

    /**
     * Display the specified payment.
     */
    public function show($id)
    {
        $payment = Payment::with([
            'user', 
            'appointment.appointmentDetails.serviceVariant.service', 
            'order.orderDetails.product',
            'appointment.employee'
        ])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }
}
