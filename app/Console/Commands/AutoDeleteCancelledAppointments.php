<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AppointmentService;

class AutoDeleteCancelledAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:auto-delete-cancelled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động xóa các lịch hẹn đã hủy sau 7 ngày';

    protected $appointmentService;

    /**
     * Create a new command instance.
     */
    public function __construct(AppointmentService $appointmentService)
    {
        parent::__construct();
        $this->appointmentService = $appointmentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu tự động xóa lịch hẹn đã hủy sau 7 ngày...');
        
        $deletedCount = $this->appointmentService->autoDeleteOldCancelled();
        
        $this->info("Đã xóa {$deletedCount} lịch hẹn đã hủy.");
        
        return Command::SUCCESS;
    }
}

