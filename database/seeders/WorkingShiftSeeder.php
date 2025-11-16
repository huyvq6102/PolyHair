<?php

namespace Database\Seeders;

use App\Models\WorkingShift;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class WorkingShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Ca sáng',
                'start_time' => '08:00:00',
                'end_time' => '15:00:00',
                'duration' => 420, // 7 hours * 60 minutes
            ],
            [
                'name' => 'Ca chiều',
                'start_time' => '15:00:00',
                'end_time' => '20:00:00',
                'duration' => 300, // 5 hours * 60 minutes
            ],
            [
                'name' => 'Ca tối',
                'start_time' => '16:00:00',
                'end_time' => '20:00:00',
                'duration' => 240, // 4 hours * 60 minutes
            ],
            [
                'name' => 'Ca đêm',
                'start_time' => '17:00:00',
                'end_time' => '22:00:00',
                'duration' => 300, // 5 hours * 60 minutes
            ],
        ];

        foreach ($shifts as $shift) {
            WorkingShift::updateOrCreate(
                [
                    'name' => $shift['name'],
                ],
                [
                    'start_time' => $shift['start_time'],
                    'end_time' => $shift['end_time'],
                    'duration' => $shift['duration'],
                ]
            );
        }

        $this->command->info('Working shifts created successfully!');
    }
}

