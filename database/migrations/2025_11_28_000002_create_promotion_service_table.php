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
        if (!Schema::hasTable('promotion_service')) {
            Schema::create('promotion_service', function (Blueprint $table) {
                $table->id();
                $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
                $table->foreignId('service_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('combo_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('service_variant_id')->nullable()->constrained()->cascadeOnDelete();
                $table->timestamps();

                // Đảm bảo chỉ có một trong ba loại được chọn
                $table->unique(['promotion_id', 'service_id']);
                $table->unique(['promotion_id', 'combo_id']);
                $table->unique(['promotion_id', 'service_variant_id']);
            });
        } else {
            // Bảng đã tồn tại, thêm các cột mới
            Schema::table('promotion_service', function (Blueprint $table) {
                // Sửa service_id thành nullable nếu chưa phải
                if (Schema::hasColumn('promotion_service', 'service_id')) {
                    DB::statement('ALTER TABLE `promotion_service` MODIFY COLUMN `service_id` BIGINT UNSIGNED NULL');
                }
                
                // Thêm combo_id nếu chưa có
                if (!Schema::hasColumn('promotion_service', 'combo_id')) {
                    $table->foreignId('combo_id')->nullable()->after('service_id')->constrained()->cascadeOnDelete();
                }
                
                // Thêm service_variant_id nếu chưa có
                if (!Schema::hasColumn('promotion_service', 'service_variant_id')) {
                    $table->foreignId('service_variant_id')->nullable()->after('combo_id')->constrained()->cascadeOnDelete();
                }
            });
            
            // Thêm unique constraints mới
            try {
                DB::statement('ALTER TABLE `promotion_service` ADD UNIQUE KEY `promotion_service_promotion_combo_unique` (`promotion_id`, `combo_id`)');
            } catch (\Exception $e) {
                // Bỏ qua nếu đã tồn tại
            }
            
            try {
                DB::statement('ALTER TABLE `promotion_service` ADD UNIQUE KEY `promotion_service_promotion_variant_unique` (`promotion_id`, `service_variant_id`)');
            } catch (\Exception $e) {
                // Bỏ qua nếu đã tồn tại
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_service');
    }
};

