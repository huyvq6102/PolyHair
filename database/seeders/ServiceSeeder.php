<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceVariant;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo Service Categories nếu chưa có
        $categories = [
            [
                'name' => 'Cắt tóc',
                'description' => 'Dịch vụ cắt tóc chuyên nghiệp'
            ],
            [
                'name' => 'Nhuộm tóc',
                'description' => 'Dịch vụ nhuộm tóc đa dạng màu sắc'
            ],
            [
                'name' => 'Uốn tóc',
                'description' => 'Dịch vụ uốn tóc tạo kiểu'
            ],
            [
                'name' => 'Chăm sóc tóc',
                'description' => 'Dịch vụ chăm sóc và phục hồi tóc'
            ],
        ];

        $categoryIds = [];
        foreach ($categories as $catData) {
            $category = ServiceCategory::firstOrCreate(
                ['name' => $catData['name']],
                $catData
            );
            $categoryIds[] = $category->id;
        }

        // Tạo Services
        $services = [
            [
                'name' => 'Cắt tóc nam',
                'description' => 'Dịch vụ cắt tóc nam chuyên nghiệp với nhiều kiểu dáng hiện đại',
                'category_id' => $categoryIds[0],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Cắt tóc nữ',
                'description' => 'Dịch vụ cắt tóc nữ với các kiểu tóc thời trang, phù hợp mọi khuôn mặt',
                'category_id' => $categoryIds[0],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Nhuộm tóc màu nâu',
                'description' => 'Nhuộm tóc màu nâu tự nhiên, bền màu, không gây hại cho tóc',
                'category_id' => $categoryIds[1],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Nhuộm tóc màu vàng',
                'description' => 'Nhuộm tóc màu vàng sáng, tạo phong cách trẻ trung, năng động',
                'category_id' => $categoryIds[1],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Uốn tóc xoăn',
                'description' => 'Uốn tóc xoăn tự nhiên, giữ nếp lâu, phù hợp mọi độ dài tóc',
                'category_id' => $categoryIds[2],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Uốn tóc sóng',
                'description' => 'Uốn tóc sóng nhẹ nhàng, tạo vẻ đẹp quyến rũ, thanh lịch',
                'category_id' => $categoryIds[2],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Gội đầu dưỡng tóc',
                'description' => 'Dịch vụ gội đầu và dưỡng tóc chuyên sâu, phục hồi tóc hư tổn',
                'category_id' => $categoryIds[3],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
            [
                'name' => 'Ủ tóc phục hồi',
                'description' => 'Dịch vụ ủ tóc phục hồi với các sản phẩm cao cấp, giúp tóc mềm mượt',
                'category_id' => $categoryIds[3],
                'image' => 'default.jpg',
                'status' => 'Hoạt động'
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );

            // Tạo Service Variants cho mỗi service
            $variants = [
                [
                    'name' => 'Cơ bản',
                    'price' => rand(50000, 200000),
                    'duration' => rand(30, 60)
                ],
                [
                    'name' => 'Cao cấp',
                    'price' => rand(200000, 500000),
                    'duration' => rand(60, 120)
                ],
            ];

            foreach ($variants as $variantData) {
                ServiceVariant::firstOrCreate(
                    [
                        'service_id' => $service->id,
                        'name' => $variantData['name']
                    ],
                    $variantData
                );
            }
        }

        $this->command->info('Đã tạo ' . count($services) . ' dịch vụ với variants thành công!');
    }
}
