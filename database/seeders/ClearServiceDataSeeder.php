<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\ServiceVariant;

class ClearServiceDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder này để xóa fake data được tạo bởi ServiceSeeder
     * CẢNH BÁO: Chỉ chạy khi bạn muốn xóa tất cả dữ liệu fake
     */
    public function run(): void
    {
        // Danh sách các service categories fake cần xóa
        $fakeCategories = [
            'Cắt tóc',
            'Nhuộm tóc',
            'Uốn tóc',
            'Chăm sóc tóc'
        ];

        // Xóa các service categories fake và tất cả services liên quan
        foreach ($fakeCategories as $categoryName) {
            $category = ServiceCategory::where('name', $categoryName)->first();
            
            if ($category) {
                // Xóa tất cả service variants của các services trong category này
                $services = Service::where('category_id', $category->id)->get();
                foreach ($services as $service) {
                    ServiceVariant::where('service_id', $service->id)->delete();
                }
                
                // Xóa tất cả services trong category này
                Service::where('category_id', $category->id)->delete();
                
                // Xóa category
                $category->delete();
            }
        }

        $this->command->info('Đã xóa tất cả fake data thành công!');
    }
}
