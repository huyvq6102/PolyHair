<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments
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
            'appointment.employee',
            'appointment.promotionUsages.promotion'
        ])->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Export payments to CSV (Excel compatible).
     */
    public function export()
    {
        $fileName = 'danh-sach-hoa-don-' . date('d-m-Y') . '.csv';
        $payments = Payment::with(['user', 'appointment', 'order'])->latest()->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($payments) {
            $file = fopen('php://output', 'w');
            
            // Thêm BOM để Excel nhận diện tiếng Việt (UTF-8)
            fwrite($file, "\xEF\xBB\xBF");

            // Tiêu đề cột
            fputcsv($file, ['ID', 'Mã Hóa Đơn', 'Khách Hàng', 'SĐT', 'Loại', 'Tổng Tiền', 'Ngày Tạo', 'Người Lập']);

            foreach ($payments as $payment) {
                // Xác định loại hóa đơn
                $type = 'Khác';
                if ($payment->appointment_id) $type = 'Dịch vụ';
                elseif ($payment->order_id) $type = 'Đơn hàng';

                fputcsv($file, [
                    $payment->id,
                    $payment->invoice_code,
                    $payment->user->name ?? 'Khách vãng lai',
                    $payment->user->phone ?? '',
                    $type,
                    number_format($payment->total, 0, '', '.'), // Format số tiền dễ đọc
                    $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : '',
                    $payment->created_by
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
