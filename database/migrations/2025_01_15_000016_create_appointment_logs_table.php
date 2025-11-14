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
        Schema::create('appointment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->enum('status_from', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'])->nullable();
            $table->enum('status_to', ['Chờ xử lý', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'])->nullable();
            $table->foreignId('modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment_logs');
    }
};

