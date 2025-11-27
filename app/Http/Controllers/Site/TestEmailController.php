<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Mail\AppointmentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmailController extends Controller
{
    public function testEmail(Request $request)
    {
        try {
            // Lấy appointment mới nhất để test
            $appointment = Appointment::with([
                'user',
                'employee.user',
                'appointmentDetails.serviceVariant.service',
                'appointmentDetails.combo'
            ])->latest()->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy appointment nào để test'
                ], 404);
            }

            $email = $request->input('email', $appointment->user->email ?? 'test@example.com');

            // Gửi email test
            Mail::to($email)->send(new AppointmentConfirmationMail($appointment));

            return response()->json([
                'success' => true,
                'message' => 'Email đã được gửi thành công đến: ' . $email,
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from' => config('mail.from'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi email: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from' => config('mail.from'),
                ]
            ], 500);
        }
    }
}

