<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Một số DB cũ có thể tạo cột không cho phép NULL, nên dùng raw SQL để nới lỏng constraint.
        if (Schema::hasTable('promotions')) {
            // discount_percent nullable
            try {
                DB::statement('ALTER TABLE promotions MODIFY discount_percent TINYINT UNSIGNED NULL');
            } catch (\Throwable $e) {
                // Ignore if fails (ví dụ đã đúng kiểu)
            }

            // discount_amount nullable
            try {
                DB::statement('ALTER TABLE promotions MODIFY discount_amount DECIMAL(10,2) NULL');
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không ép buộc quay lại NOT NULL để tránh làm hỏng dữ liệu hiện có
    }
};


