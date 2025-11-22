<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkingShift;
use Illuminate\Support\Facades\DB;

class WorkingShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Thêm các ca làm việc (chỉ thêm nếu chưa tồn tại)
        $shifts = [
            [
                'name' => 'Ca sáng',
                'start_time' => '07:00:00',
                'end_time' => '12:00:00',
                'duration' => 5, // 5 giờ
            ],
            [
                'name' => 'Ca chiều',
                'start_time' => '12:00:00',
                'end_time' => '17:00:00',
                'duration' => 5, // 5 giờ
            ],
            [
                'name' => 'Ca tối',
                'start_time' => '17:00:00',
                'end_time' => '22:00:00',
                'duration' => 5, // 5 giờ
            ],
        ];

        $added = 0;
        foreach ($shifts as $shift) {
            // Kiểm tra xem ca làm việc đã tồn tại chưa (dựa vào start_time)
            $exists = WorkingShift::where('start_time', $shift['start_time'])->exists();
            
            if (!$exists) {
                WorkingShift::create($shift);
                $added++;
            }
        }

        if ($added > 0) {
            $this->command->info("Đã thêm {$added} ca làm việc mới vào database!");
        } else {
            $this->command->info('Tất cả các ca làm việc đã tồn tại trong database!');
        }
    }
}
