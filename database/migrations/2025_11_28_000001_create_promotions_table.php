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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();

            // Thông tin cơ bản
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Loại giảm giá: percent | amount
            $table->string('discount_type', 20)->default('percent');

            // Giá trị giảm theo %
            $table->unsignedTinyInteger('discount_percent')->nullable();

            // Giá trị giảm theo số tiền
            $table->decimal('discount_amount', 10, 2)->nullable();

            // Phạm vi áp dụng: service | order
            $table->string('apply_scope', 20)->default('service');

            // Nếu áp dụng theo hóa đơn: tổng tiền tối thiểu để được dùng
            $table->decimal('min_order_amount', 10, 2)->nullable();

            // Giảm tối đa bao nhiêu tiền (dùng cho giảm theo %)
            $table->decimal('max_discount_amount', 10, 2)->nullable();

            // Mỗi tài khoản được dùng tối đa bao nhiêu lần
            $table->unsignedInteger('per_user_limit')->nullable();

            // Thời gian hiệu lực
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Trạng thái: inactive | active | scheduled | expired
            $table->string('status', 50)->default('inactive');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};


