<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tạo 5 danh mục dịch vụ
        $categories = [
            [
                'name' => 'Cắt tóc',
                'slug' => Str::slug('Cắt tóc'),
                'description' => 'Các dịch vụ cắt tóc nam, nữ chuyên nghiệp',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Gội đầu',
                'slug' => Str::slug('Gội đầu'),
                'description' => 'Dịch vụ gội đầu và chăm sóc da đầu',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Nhuộm tóc',
                'slug' => Str::slug('Nhuộm tóc'),
                'description' => 'Nhuộm tóc, tẩy tóc, highlight chuyên nghiệp',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Massage',
                'slug' => Str::slug('Massage'),
                'description' => 'Massage da đầu, cổ vai gáy thư giãn',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Chăm sóc da',
                'slug' => Str::slug('Chăm sóc da'),
                'description' => 'Chăm sóc da mặt, đắp mặt nạ',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $categoryData) {
            $category = ServiceCategory::create($categoryData);
            $categoryIds[] = $category->id;
        }

        $this->command->info('Đã tạo 5 danh mục dịch vụ');

        // 2. Tạo 5 dịch vụ
        $services = [
            [
                'service_code' => 'SV001',
                'category_id' => $categoryIds[0], // Cắt tóc
                'name' => 'Cắt tóc nam',
                'slug' => Str::slug('Cắt tóc nam'),
                'description' => 'Cắt tóc nam theo phong cách hiện đại, phù hợp với khuôn mặt',
                'status' => 'Hoạt động',
                'base_price' => 80000,
                'base_duration' => 30,
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'service_code' => 'SV002',
                'category_id' => $categoryIds[0], // Cắt tóc
                'name' => 'Cắt tóc nữ',
                'slug' => Str::slug('Cắt tóc nữ'),
                'description' => 'Cắt tóc nữ đa dạng kiểu dáng, từ ngắn đến dài',
                'status' => 'Hoạt động',
                'base_price' => 120000,
                'base_duration' => 45,
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'service_code' => 'SV003',
                'category_id' => $categoryIds[1], // Gội đầu
                'name' => 'Gội đầu dưỡng tóc',
                'slug' => Str::slug('Gội đầu dưỡng tóc'),
                'description' => 'Gội đầu với dầu gội cao cấp và dưỡng tóc sâu',
                'status' => 'Hoạt động',
                'base_price' => 100000,
                'base_duration' => 20,
                'sort_order' => 3,
                'is_featured' => false,
            ],
            [
                'service_code' => 'SV004',
                'category_id' => $categoryIds[2], // Nhuộm tóc
                'name' => 'Nhuộm tóc toàn đầu',
                'slug' => Str::slug('Nhuộm tóc toàn đầu'),
                'description' => 'Nhuộm tóc toàn đầu với thuốc nhuộm không amoniac',
                'status' => 'Hoạt động',
                'base_price' => 500000,
                'base_duration' => 120,
                'sort_order' => 4,
                'is_featured' => true,
            ],
            [
                'service_code' => 'SV005',
                'category_id' => $categoryIds[3], // Massage
                'name' => 'Massage da đầu',
                'slug' => Str::slug('Massage da đầu'),
                'description' => 'Massage da đầu giúp thư giãn và kích thích mọc tóc',
                'status' => 'Hoạt động',
                'base_price' => 150000,
                'base_duration' => 30,
                'sort_order' => 5,
                'is_featured' => false,
            ],
        ];

        $serviceIds = [];
        foreach ($services as $serviceData) {
            $service = Service::create($serviceData);
            $serviceIds[] = $service->id;
        }

        $this->command->info('Đã tạo 5 dịch vụ');

        // 3. Tạo 5 người dùng (2 admin, 2 nhân viên, 1 khách hàng)
        $users = [
            [
                'name' => 'Nguyễn Văn Admin',
                'email' => 'admin1@polyhair.com',
                'password' => Hash::make('password123'),
                'phone' => '0912345678',
                'gender' => 'Nam',
                'dob' => '1990-01-15',
                'status' => 'Hoạt động',
                'role_id' => 1, // admin
            ],
            [
                'name' => 'Trần Thị Quản lý',
                'email' => 'admin2@polyhair.com',
                'password' => Hash::make('password123'),
                'phone' => '0923456789',
                'gender' => 'Nữ',
                'dob' => '1988-05-20',
                'status' => 'Hoạt động',
                'role_id' => 1, // admin
            ],
            [
                'name' => 'Lê Văn Stylist',
                'email' => 'stylist1@polyhair.com',
                'password' => Hash::make('password123'),
                'phone' => '0934567890',
                'gender' => 'Nam',
                'dob' => '1995-08-10',
                'status' => 'Hoạt động',
                'role_id' => 2, // nhân viên
            ],
            [
                'name' => 'Phạm Thị Barber',
                'email' => 'barber1@polyhair.com',
                'password' => Hash::make('password123'),
                'phone' => '0945678901',
                'gender' => 'Nữ',
                'dob' => '1993-12-25',
                'status' => 'Hoạt động',
                'role_id' => 2, // nhân viên
            ],
            [
                'name' => 'Hoàng Văn Khách',
                'email' => 'customer1@example.com',
                'password' => Hash::make('password123'),
                'phone' => '0956789012',
                'gender' => 'Nam',
                'dob' => '1998-03-30',
                'status' => 'Hoạt động',
                'role_id' => 3, // khách hàng
            ],
        ];

        $userIds = [];
        foreach ($users as $userData) {
            $user = User::create($userData);
            $userIds[] = $user->id;
        }

        $this->command->info('Đã tạo 5 người dùng');

        // 4. Tạo 5 nhân viên (liên kết với 2 users nhân viên và 2 users admin)
        $employees = [
            [
                'user_id' => $userIds[2], // Lê Văn Stylist
                'gender' => 'Nam',
                'dob' => '1995-08-10',
                'position' => 'Stylist',
                'level' => 'Senior',
                'experience_years' => 8,
                'bio' => 'Chuyên gia cắt tóc nam với hơn 8 năm kinh nghiệm. Đã từng làm việc tại nhiều salon nổi tiếng.',
                'status' => 'Đang làm việc',
            ],
            [
                'user_id' => $userIds[3], // Phạm Thị Barber
                'gender' => 'Nữ',
                'dob' => '1993-12-25',
                'position' => 'Barber',
                'level' => 'Middle',
                'experience_years' => 5,
                'bio' => 'Chuyên về cắt tóc nữ và nhuộm màu. Có khả năng tư vấn phong cách phù hợp với từng khách hàng.',
                'status' => 'Đang làm việc',
            ],
            [
                'user_id' => $userIds[0], // Nguyễn Văn Admin (có thể là quản lý)
                'gender' => 'Nam',
                'dob' => '1990-01-15',
                'position' => 'Stylist',
                'level' => 'Senior',
                'experience_years' => 10,
                'bio' => 'Quản lý và stylist chính của salon. Có nhiều năm kinh nghiệm trong ngành làm đẹp.',
                'status' => 'Đang làm việc',
            ],
            [
                'user_id' => $userIds[1], // Trần Thị Quản lý
                'gender' => 'Nữ',
                'dob' => '1988-05-20',
                'position' => 'Stylist',
                'level' => 'Senior',
                'experience_years' => 12,
                'bio' => 'Quản lý salon với chuyên môn về nhuộm tóc và chăm sóc tóc cao cấp.',
                'status' => 'Đang làm việc',
            ],
            [
                'user_id' => null, // Nhân viên không có user account
                'gender' => 'Nam',
                'dob' => '1997-06-15',
                'position' => 'Shampooer',
                'level' => 'Junior',
                'experience_years' => 2,
                'bio' => 'Chuyên viên gội đầu và massage da đầu. Phục vụ tận tình, chu đáo.',
                'status' => 'Đang làm việc',
            ],
        ];

        $employeeIds = [];
        foreach ($employees as $employeeData) {
            $employee = Employee::create($employeeData);
            $employeeIds[] = $employee->id;
        }

        $this->command->info('Đã tạo 5 nhân viên');

        // 5. Gán dịch vụ cho nhân viên (employee_skills)
        // Nhân viên 1 (Stylist) - có thể làm: Cắt tóc nam, Cắt tóc nữ, Nhuộm tóc
        DB::table('employee_skills')->insert([
            ['employee_id' => $employeeIds[0], 'service_id' => $serviceIds[0]], // Cắt tóc nam
            ['employee_id' => $employeeIds[0], 'service_id' => $serviceIds[1]], // Cắt tóc nữ
            ['employee_id' => $employeeIds[0], 'service_id' => $serviceIds[3]], // Nhuộm tóc
        ]);

        // Nhân viên 2 (Barber) - có thể làm: Cắt tóc nữ, Nhuộm tóc
        DB::table('employee_skills')->insert([
            ['employee_id' => $employeeIds[1], 'service_id' => $serviceIds[1]], // Cắt tóc nữ
            ['employee_id' => $employeeIds[1], 'service_id' => $serviceIds[3]], // Nhuộm tóc
        ]);

        // Nhân viên 3 (Admin/Stylist) - có thể làm tất cả
        DB::table('employee_skills')->insert([
            ['employee_id' => $employeeIds[2], 'service_id' => $serviceIds[0]], // Cắt tóc nam
            ['employee_id' => $employeeIds[2], 'service_id' => $serviceIds[1]], // Cắt tóc nữ
            ['employee_id' => $employeeIds[2], 'service_id' => $serviceIds[3]], // Nhuộm tóc
            ['employee_id' => $employeeIds[2], 'service_id' => $serviceIds[4]], // Massage
        ]);

        // Nhân viên 4 (Quản lý) - chuyên về nhuộm và chăm sóc
        DB::table('employee_skills')->insert([
            ['employee_id' => $employeeIds[3], 'service_id' => $serviceIds[2]], // Gội đầu
            ['employee_id' => $employeeIds[3], 'service_id' => $serviceIds[3]], // Nhuộm tóc
            ['employee_id' => $employeeIds[3], 'service_id' => $serviceIds[4]], // Massage
        ]);

        // Nhân viên 5 (Shampooer) - chuyên gội đầu và massage
        DB::table('employee_skills')->insert([
            ['employee_id' => $employeeIds[4], 'service_id' => $serviceIds[2]], // Gội đầu
            ['employee_id' => $employeeIds[4], 'service_id' => $serviceIds[4]], // Massage
        ]);

        $this->command->info('Đã gán dịch vụ cho nhân viên');
        $this->command->info('Hoàn thành! Đã tạo dữ liệu mẫu cho tất cả các bảng.');
    }
}

