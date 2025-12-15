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
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('Chờ xử lý', 'Chờ xác nhận', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy', 'Chưa thanh toán', 'Đã thanh toán') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy', 'Chưa thanh toán', 'Đã thanh toán') NULL");
    }
};
