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
        // Bổ sung các cột còn thiếu cho bảng promotions để khớp với model/service hiện tại
        Schema::table('promotions', function (Blueprint $table) {
            if (!Schema::hasColumn('promotions', 'discount_type')) {
                $table->string('discount_type', 20)
                    ->default('percent')
                    ->after('description');
            }

            if (!Schema::hasColumn('promotions', 'discount_percent')) {
                $table->unsignedTinyInteger('discount_percent')
                    ->nullable()
                    ->after('discount_type');
            }

            if (!Schema::hasColumn('promotions', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)
                    ->nullable()
                    ->after('discount_percent');
            }

            if (!Schema::hasColumn('promotions', 'apply_scope')) {
                $table->string('apply_scope', 20)
                    ->default('service')
                    ->after('discount_amount');
            }

            if (!Schema::hasColumn('promotions', 'min_order_amount')) {
                $table->decimal('min_order_amount', 10, 2)
                    ->nullable()
                    ->after('apply_scope');
            }

            if (!Schema::hasColumn('promotions', 'max_discount_amount')) {
                $table->decimal('max_discount_amount', 10, 2)
                    ->nullable()
                    ->after('min_order_amount');
            }

            if (!Schema::hasColumn('promotions', 'per_user_limit')) {
                $table->unsignedInteger('per_user_limit')
                    ->nullable()
                    ->after('max_discount_amount');
            }

            if (!Schema::hasColumn('promotions', 'start_date')) {
                $table->date('start_date')
                    ->nullable()
                    ->after('per_user_limit');
            }

            if (!Schema::hasColumn('promotions', 'end_date')) {
                $table->date('end_date')
                    ->nullable()
                    ->after('start_date');
            }

            if (!Schema::hasColumn('promotions', 'status')) {
                $table->string('status', 50)
                    ->default('inactive')
                    ->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('promotions', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('promotions', 'start_date')) {
                $table->dropColumn('start_date');
            }
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
            if (Schema::hasColumn('promotions', 'discount_percent')) {
                $table->dropColumn('discount_percent');
            }
            if (Schema::hasColumn('promotions', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};


