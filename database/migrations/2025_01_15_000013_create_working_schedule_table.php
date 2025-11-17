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
        Schema::create('working_schedule', function (Blueprint $table) {
            $table->id();

            // Nhân viên (employees.id)
            $table->foreignId('employee_id')
                ->nullable()
                ->constrained('employees')
                ->onDelete('cascade');

            // Ngày làm việc
            $table->date('work_date')->nullable();

            // Ca làm việc (working_shifts.id)
            $table->foreignId('shift_id')
                ->nullable()
                ->constrained('working_shifts')
                ->onDelete('set null');

            // Trạng thái lịch: rảnh / bận / nghỉ
            $table->enum('status', ['available', 'busy', 'off'])->nullable();

            // Ảnh minh họa lịch (nếu cần)
            $table->string('image')->nullable();

            // Cờ bàn giao (theo schema gốc MySQL, mặc định 0)
            $table->boolean('is_handover')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_schedule');
    }
};


