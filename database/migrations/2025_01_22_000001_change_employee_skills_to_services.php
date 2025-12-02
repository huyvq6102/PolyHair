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
        Schema::table('employee_skills', function (Blueprint $table) {
            // Xóa foreign key cũ
            $table->dropForeign(['skill_id']);
            // Xóa cột skill_id
            $table->dropColumn('skill_id');
            // Thêm cột service_id
            $table->foreignId('service_id')->nullable()->after('employee_id')->constrained('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_skills', function (Blueprint $table) {
            // Xóa foreign key mới
            $table->dropForeign(['service_id']);
            // Xóa cột service_id
            $table->dropColumn('service_id');
            // Thêm lại cột skill_id
            $table->foreignId('skill_id')->nullable()->after('employee_id')->constrained('skills')->onDelete('cascade');
        });
    }
};

