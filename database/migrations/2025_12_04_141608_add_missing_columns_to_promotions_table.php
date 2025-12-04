<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Thêm cột discount_type nếu chưa có
            if (!Schema::hasColumn('promotions', 'discount_type')) {
                $table->string('discount_type', 20)->default('percent')->after('description');
            }
            
            // Thêm cột discount_amount nếu chưa có
            if (!Schema::hasColumn('promotions', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->nullable()->after('discount_percent');
            }
            
            // Thêm cột apply_scope nếu chưa có
            if (!Schema::hasColumn('promotions', 'apply_scope')) {
                $table->string('apply_scope', 20)->default('service')->after('discount_amount');
            }
            
            // Thêm cột min_order_amount nếu chưa có
            if (!Schema::hasColumn('promotions', 'min_order_amount')) {
                $table->decimal('min_order_amount', 10, 2)->nullable()->after('apply_scope');
            }
            
            // Thêm cột max_discount_amount nếu chưa có
            if (!Schema::hasColumn('promotions', 'max_discount_amount')) {
                $table->decimal('max_discount_amount', 10, 2)->nullable()->after('min_order_amount');
            }
            
            // Thêm cột per_user_limit nếu chưa có
            if (!Schema::hasColumn('promotions', 'per_user_limit')) {
                $table->unsignedInteger('per_user_limit')->nullable()->after('max_discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'per_user_limit')) {
                $table->dropColumn('per_user_limit');
            }
            if (Schema::hasColumn('promotions', 'max_discount_amount')) {
                $table->dropColumn('max_discount_amount');
            }
            if (Schema::hasColumn('promotions', 'min_order_amount')) {
                $table->dropColumn('min_order_amount');
            }
            if (Schema::hasColumn('promotions', 'apply_scope')) {
                $table->dropColumn('apply_scope');
            }
            if (Schema::hasColumn('promotions', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
            if (Schema::hasColumn('promotions', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};
