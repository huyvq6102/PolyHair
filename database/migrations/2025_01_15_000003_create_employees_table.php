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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('avatar', 255)->nullable();
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable();
            $table->date('dob')->nullable();
            $table->enum('position', ['Stylist', 'Barber', 'Shampooer', 'Receptionist'])->nullable();
            $table->enum('level', ['Intern', 'Junior', 'Middle', 'Senior'])->nullable();
            $table->tinyInteger('experience_years')->nullable();
            $table->text('bio')->nullable();
            $table->enum('status', ['Đang làm việc', 'Nghỉ phép', 'Vô hiệu hóa'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

