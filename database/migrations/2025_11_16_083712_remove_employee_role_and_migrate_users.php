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
        // Tìm role "Nhân Viên" hoặc "Nhân viên"
        $nhanVienRole = DB::table('roles')
            ->where('name', 'Nhân Viên')
            ->orWhere('name', 'Nhân viên')
            ->first();

        if ($nhanVienRole) {
            // Cập nhật tất cả users có role_id là Employee (id=4) sang role "Nhân Viên"
            DB::table('users')
                ->where('role_id', 4) // Employee role id
                ->update(['role_id' => $nhanVienRole->id]);
        }

        // Xóa role Employee
        DB::table('roles')->where('id', 4)->where('name', 'Employee')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tạo lại role Employee nếu cần rollback
        $employeeRoleId = DB::table('roles')->insertGetId([
            'name' => 'Employee',
            'description' => 'Employee role for staff members',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tìm role "Nhân Viên"
        $nhanVienRole = DB::table('roles')
            ->where('name', 'Nhân Viên')
            ->orWhere('name', 'Nhân viên')
            ->first();

        if ($nhanVienRole) {
            // Chuyển lại users về Employee (không chính xác 100% nhưng đây là rollback)
            // Lưu ý: Không thể biết chính xác user nào đã là Employee trước đó
        }
    }
};
