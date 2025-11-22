<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\WorkingShift;
use App\Models\WorkingSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmployeeScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo role nhân viên nếu chưa có
        $employeeRole = Role::firstOrCreate(
            ['name' => 'Nhân viên'],
            ['description' => 'Nhân viên làm việc tại salon']
        );

        // Tạo các ca làm việc (shifts)
        $shifts = [
            [
                'name' => 'Ca sáng',
                'start_time' => Carbon::createFromTime(7, 0, 0),
                'end_time' => Carbon::createFromTime(12, 0, 0),
                'duration' => 300, // 5 giờ
            ],
            [
                'name' => 'Ca chiều',
                'start_time' => Carbon::createFromTime(12, 0, 0),
                'end_time' => Carbon::createFromTime(17, 0, 0),
                'duration' => 300, // 5 giờ
            ],
            [
                'name' => 'Ca tối',
                'start_time' => Carbon::createFromTime(17, 0, 0),
                'end_time' => Carbon::createFromTime(22, 0, 0),
                'duration' => 300, // 5 giờ
            ],
            [
                'name' => 'Ca cả ngày',
                'start_time' => Carbon::createFromTime(7, 0, 0),
                'end_time' => Carbon::createFromTime(22, 0, 0),
                'duration' => 900, // 15 giờ
            ],
        ];

        $shiftIds = [];
        foreach ($shifts as $shiftData) {
            $shift = WorkingShift::firstOrCreate(
                ['name' => $shiftData['name']],
                $shiftData
            );
            $shiftIds[$shiftData['name']] = $shift->id;
        }

        // Tạo nhân viên
        $employees = [
            [
                'name' => 'Nguyễn Văn An',
                'email' => 'nguyenvanan@example.com',
                'phone' => '0912345678',
                'position' => 'Stylist',
                'level' => 'Senior',
                'experience_years' => 5,
                'bio' => 'Chuyên gia cắt tóc và tạo kiểu với hơn 5 năm kinh nghiệm',
            ],
            [
                'name' => 'Trần Thị Bình',
                'email' => 'tranthibinh@example.com',
                'phone' => '0923456789',
                'position' => 'Barber',
                'level' => 'Middle',
                'experience_years' => 3,
                'bio' => 'Thợ cắt tóc nam chuyên nghiệp',
            ],
            [
                'name' => 'Lê Văn Cường',
                'email' => 'levancuong@example.com',
                'phone' => '0934567890',
                'position' => 'Stylist',
                'level' => 'Junior',
                'experience_years' => 1,
                'bio' => 'Nhân viên mới, nhiệt tình và chăm chỉ',
            ],
            [
                'name' => 'Phạm Thị Dung',
                'email' => 'phamthidung@example.com',
                'phone' => '0945678901',
                'position' => 'Shampooer',
                'level' => 'Middle',
                'experience_years' => 2,
                'bio' => 'Chuyên viên gội đầu và chăm sóc tóc',
            ],
        ];

        $employeeIds = [];
        foreach ($employees as $empData) {
            // Tạo user cho nhân viên
            $user = User::firstOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['name'],
                    'email' => $empData['email'],
                    'phone' => $empData['phone'],
                    'password' => Hash::make('123456'), // Password mặc định
                    'role_id' => $employeeRole->id,
                    'status' => 'Hoạt động',
                ]
            );

            // Tạo employee
            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'position' => $empData['position'],
                    'level' => $empData['level'],
                    'experience_years' => $empData['experience_years'],
                    'bio' => $empData['bio'],
                    'status' => 'Đang làm việc',
                ]
            );

            $employeeIds[] = $employee->id;
        }

        // Tạo lịch làm việc cho các nhân viên trong 30 ngày tới
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        foreach ($employeeIds as $index => $employeeId) {
            $currentDate = $startDate->copy();
            
            while ($currentDate->lte($endDate)) {
                // Bỏ qua chủ nhật (ngày 0)
                if ($currentDate->dayOfWeek !== Carbon::SUNDAY) {
                    // Mỗi nhân viên có lịch làm việc khác nhau
                    if ($index === 0) {
                        // Nhân viên 1: Làm ca sáng và ca chiều vào thứ 2-6
                        if ($currentDate->dayOfWeek >= Carbon::MONDAY && $currentDate->dayOfWeek <= Carbon::FRIDAY) {
                            // Ca sáng
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca sáng'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                            
                            // Ca chiều
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca chiều'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                        }
                    } elseif ($index === 1) {
                        // Nhân viên 2: Làm ca chiều và ca tối vào thứ 2-6
                        if ($currentDate->dayOfWeek >= Carbon::MONDAY && $currentDate->dayOfWeek <= Carbon::FRIDAY) {
                            // Ca chiều
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca chiều'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                            
                            // Ca tối
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca tối'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                        }
                    } elseif ($index === 2) {
                        // Nhân viên 3: Làm ca cả ngày vào thứ 2, 4, 6
                        if (in_array($currentDate->dayOfWeek, [Carbon::MONDAY, Carbon::WEDNESDAY, Carbon::FRIDAY])) {
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca cả ngày'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                        }
                    } else {
                        // Nhân viên 4: Làm ca sáng vào thứ 3, 5, 7
                        if (in_array($currentDate->dayOfWeek, [Carbon::TUESDAY, Carbon::THURSDAY, Carbon::SATURDAY])) {
                            WorkingSchedule::firstOrCreate(
                                [
                                    'employee_id' => $employeeId,
                                    'work_date' => $currentDate->format('Y-m-d'),
                                    'shift_id' => $shiftIds['Ca sáng'],
                                ],
                                [
                                    'status' => 'available',
                                ]
                            );
                        }
                    }
                }
                
                $currentDate->addDay();
            }
        }

        $this->command->info('Đã tạo dữ liệu fake cho nhân viên và lịch làm việc thành công!');
        $this->command->info('Tổng số nhân viên: ' . count($employeeIds));
        $this->command->info('Tổng số ca làm việc: ' . count($shifts));
        $this->command->info('Lịch làm việc đã được tạo cho 30 ngày tới');
    }
}

