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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'remember_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->enum('gender', ['Nam', 'Nữ', 'Khác'])->nullable()->after('avatar');
            $table->date('dob')->nullable()->after('gender');
            $table->enum('status', ['Hoạt động', 'Vô hiệu hóa', 'Cấm'])->nullable()->after('dob');
            $table->foreignId('role_id')->nullable()->after('status')->constrained('roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['phone', 'avatar', 'gender', 'dob', 'status', 'role_id', 'deleted_at']);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};

