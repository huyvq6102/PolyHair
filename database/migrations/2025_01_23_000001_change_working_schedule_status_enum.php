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
        // Bước 1: Mở rộng enum để bao gồm cả giá trị cũ và mới
        DB::statement("ALTER TABLE working_schedule MODIFY COLUMN status ENUM('available', 'busy', 'off', 'pending', 'approved', 'cancelled', 'completed') NULL");
        
        // Bước 2: Chuyển đổi dữ liệu cũ
        // available -> pending (mặc định)
        // busy -> approved
        // off -> cancelled
        DB::statement("UPDATE working_schedule SET status = 'pending' WHERE status = 'available'");
        DB::statement("UPDATE working_schedule SET status = 'approved' WHERE status = 'busy'");
        DB::statement("UPDATE working_schedule SET status = 'cancelled' WHERE status = 'off'");
        
        // Bước 3: Thay đổi enum chỉ giữ lại giá trị mới
        DB::statement("ALTER TABLE working_schedule MODIFY COLUMN status ENUM('pending', 'approved', 'cancelled', 'completed') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Chuyển đổi dữ liệu về giá trị cũ trước khi rollback enum
        // pending -> available
        // approved -> busy
        // cancelled -> off
        // completed -> available (mặc định)
        DB::statement("UPDATE working_schedule SET status = 'available' WHERE status = 'pending'");
        DB::statement("UPDATE working_schedule SET status = 'busy' WHERE status = 'approved'");
        DB::statement("UPDATE working_schedule SET status = 'off' WHERE status = 'cancelled'");
        DB::statement("UPDATE working_schedule SET status = 'available' WHERE status = 'completed'");
        
        // Khôi phục lại enum cũ
        DB::statement("ALTER TABLE working_schedule MODIFY COLUMN status ENUM('available', 'busy', 'off') NULL");
    }
};

