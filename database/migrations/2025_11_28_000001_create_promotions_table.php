<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedTinyInteger('discount_percent');
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('status', 50)->default('inactive');
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Bảng đã tồn tại, sửa cột status để đảm bảo đủ độ dài
            if (Schema::hasColumn('promotions', 'status')) {
                // Sửa cột status thành VARCHAR(50) nếu chưa đủ độ dài
                DB::statement('ALTER TABLE `promotions` MODIFY COLUMN `status` VARCHAR(50) DEFAULT "inactive"');
            } else {
                // Thêm cột status nếu chưa có
                Schema::table('promotions', function (Blueprint $table) {
                    $table->string('status', 50)->default('inactive')->after('end_date');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa foreign key constraints trước khi drop bảng
        if (Schema::hasTable('promotion_usages')) {
            // Xóa foreign key constraint nếu tồn tại
            try {
                DB::statement('ALTER TABLE `promotion_usages` DROP FOREIGN KEY `promotion_usages_promotion_id_foreign`');
            } catch (\Exception $e) {
                // Bỏ qua nếu constraint không tồn tại hoặc đã bị xóa
            }
        }
        
        if (Schema::hasTable('promotion_service')) {
            // Xóa foreign key constraint từ promotion_service nếu tồn tại
            try {
                DB::statement('ALTER TABLE `promotion_service` DROP FOREIGN KEY `promotion_service_promotion_id_foreign`');
            } catch (\Exception $e) {
                // Bỏ qua nếu constraint không tồn tại hoặc đã bị xóa
            }
        }
        
        Schema::dropIfExists('promotions');
    }
};

