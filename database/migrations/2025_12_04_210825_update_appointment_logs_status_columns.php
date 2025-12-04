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
        Schema::table('appointment_logs', function (Blueprint $table) {
            // Thay đổi enum thành string để hỗ trợ tất cả các trạng thái
            $table->string('status_from', 50)->nullable()->change();
            $table->string('status_to', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_logs', function (Blueprint $table) {
            // Khôi phục lại enum (chỉ với các giá trị ban đầu)
            $table->enum('status_from', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'])->nullable()->change();
            $table->enum('status_to', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'])->nullable()->change();
        });
    }
};
