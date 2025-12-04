<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Chạy mỗi phút để kiểm tra và tự động xác nhận lịch hẹn sau 30 phút
Schedule::command('appointments:auto-confirm')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
