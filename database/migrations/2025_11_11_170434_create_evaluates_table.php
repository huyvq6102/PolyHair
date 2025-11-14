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
        Schema::create('evaluates', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->tinyInteger('rating')->default(0);
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_appointment')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->foreignId('id_service')->constrained('services')->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluates');
    }
};
