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
        // Modify the enum to include more status values
        DB::statement("ALTER TABLE appointment_details MODIFY COLUMN status ENUM('Chờ', 'Xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Hủy', 'Đã hủy') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert any new statuses back to original values
        DB::table('appointment_details')
            ->where('status', 'Đang thực hiện')
            ->update(['status' => 'Xác nhận']);

        DB::table('appointment_details')
            ->where('status', 'Đã hủy')
            ->update(['status' => 'Hủy']);

        DB::statement("ALTER TABLE appointment_details MODIFY COLUMN status ENUM('Chờ', 'Xác nhận', 'Hoàn thành', 'Hủy') NULL");
    }
};
