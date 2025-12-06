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
        Schema::table('payments', function (Blueprint $table) {
            // Change the column to allow 'vnpay' as a payment type
            // Note: Modifying ENUM columns directly can be tricky with some databases (e.g., MySQL < 8.0)
            // This approach is generally safe for adding new values.
            $table->enum('payment_type', ['cash', 'online', 'vnpay', 'momo', 'zalopay'])
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Revert the column by removing 'vnpay' if it's safe to do so
            // Be careful when reverting ENUMs, as it might cause data loss if 'vnpay' values exist
            $table->enum('payment_type', ['cash', 'online'])
                  ->nullable()
                  ->change();
        });
    }
};
