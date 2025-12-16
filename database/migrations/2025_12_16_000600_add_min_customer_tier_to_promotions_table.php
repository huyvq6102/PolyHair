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
            if (!Schema::hasColumn('promotions', 'min_customer_tier')) {
                $table->string('min_customer_tier', 50)
                    ->nullable()
                    ->after('usage_limit')
                    ->comment('Áp dụng cho khách hàng từ hạng này trở lên (Khách thường, Silver, Gold, VIP)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (Schema::hasColumn('promotions', 'min_customer_tier')) {
                $table->dropColumn('min_customer_tier');
            }
        });
    }
};


