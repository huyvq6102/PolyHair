<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\AppointmentLog;
use App\Events\AppointmentStatusUpdated;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoConfirmAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:auto-confirm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động chuyển lịch hẹn từ "Chờ xử lý" sang "Đã xác nhận" sau 1 phút';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra lịch hẹn cần tự động xác nhận...');

        // Tìm các lịch hẹn có status = 'Chờ xử lý' và đã quá 1 phút kể từ khi tạo
        $cutoffTime = Carbon::now()->subMinute(1);

        $appointments = Appointment::where('status', 'Chờ xử lý')
            ->where('created_at', '<=', $cutoffTime)
            ->whereRaw('TIMESTAMPDIFF(SECOND, created_at, NOW()) >= 60') // Đảm bảo đã qua ít nhất 1 phút
            ->get();

        $count = 0;

        foreach ($appointments as $appointment) {
            try {
                DB::transaction(function () use ($appointment, &$count) {
                    $oldStatus = $appointment->status;

                    // Chuyển status sang "Đã xác nhận"
                    $appointment->update([
                        'status' => 'Đã xác nhận'
                    ]);

                    // Log status change
                    AppointmentLog::create([
                        'appointment_id' => $appointment->id,
                        'status_from' => $oldStatus,
                        'status_to' => 'Đã xác nhận',
                        'modified_by' => null, // Tự động xác nhận
                    ]);

                    // Refresh và load relationships trước khi broadcast
                    $appointment->refresh();
                    $appointment->load([
                        'user',
                        'employee.user',
                        'appointmentDetails.serviceVariant.service',
                        'appointmentDetails.combo'
                    ]);

                    // Broadcast status update event
                    event(new AppointmentStatusUpdated($appointment));

                    $count++;
                });
            } catch (\Exception $e) {
                $this->error("Lỗi khi xác nhận lịch hẹn ID {$appointment->id}: " . $e->getMessage());
            }
        }

        if ($count > 0) {
            $this->info("Đã tự động xác nhận {$count} lịch hẹn.");
        } else {
            $this->info("Không có lịch hẹn nào cần tự động xác nhận.");
        }

        return Command::SUCCESS;
    }
}
