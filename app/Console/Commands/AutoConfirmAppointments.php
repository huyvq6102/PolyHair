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
    protected $signature = 'appointments:auto-confirm {--force : Bỏ qua kiểm tra thời gian và xác nhận tất cả lịch hẹn "Chờ xử lý"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động chuyển lịch hẹn từ "Chờ xử lý" sang "Đã xác nhận" sau 30 phút';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra lịch hẹn cần tự động xác nhận...');

        // Tìm các lịch hẹn có status = 'Chờ xử lý' và đã quá 30 phút kể từ khi tạo
        $now = Carbon::now();
        $cutoffTime = $now->copy()->subMinutes(30);

        $this->info("Thời gian hiện tại: {$now->format('Y-m-d H:i:s')}");
        $this->info("Thời gian cutoff (30 phút trước): {$cutoffTime->format('Y-m-d H:i:s')}");

        // Lấy tất cả appointments có status 'Chờ xử lý'
        $allPending = Appointment::where('status', 'Chờ xử lý')->get();
        $this->info("Tổng số lịch hẹn đang 'Chờ xử lý': {$allPending->count()}");

        if ($allPending->count() > 0) {
            $this->info("\nChi tiết các lịch hẹn 'Chờ xử lý':");
            foreach ($allPending as $apt) {
                $createdAt = Carbon::parse($apt->created_at);
                $minutesSinceCreated = $createdAt->diffInMinutes($now);
                $secondsSinceCreated = $createdAt->diffInSeconds($now);
                $this->line("  - ID {$apt->id}: Tạo lúc {$createdAt->format('Y-m-d H:i:s')}, Đã qua {$minutesSinceCreated} phút ({$secondsSinceCreated} giây)");
            }
        }

        // Lọc các appointments đã quá 30 phút (hoặc tất cả nếu có --force)
        $force = $this->option('force');
        
        if ($force) {
            $this->warn("\n⚠️  Chế độ FORCE: Sẽ xác nhận TẤT CẢ lịch hẹn 'Chờ xử lý' bất kể thời gian!");
            $appointments = $allPending;
        } else {
            $appointments = $allPending->filter(function($appointment) use ($now) {
                $createdAt = Carbon::parse($appointment->created_at);
                $minutesSinceCreated = $createdAt->diffInMinutes($now);
                
                return $minutesSinceCreated >= 30;
            });
        }

        $this->info("\nSố lịch hẹn sẽ được xác nhận: {$appointments->count()}");

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
