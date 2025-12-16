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
            if (!Schema::hasColumn('promotions', 'usage_limit')) {
                $table->unsignedInteger('usage_limit')
                    ->nullable()
                    ->after('per_user_limit')
                    ->comment('Tổng số lượt mã khuyến mãi được dùng cho toàn bộ hệ thống');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'usage_limit')) {
                $table->dropColumn('usage_limit');
            }
        });
    }
};


