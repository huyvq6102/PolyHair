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
        // Drop unique index on phone column if it exists
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        $tableName = 'users';
        
        // Check if unique index exists
        $indexes = $connection->select("SHOW INDEXES FROM `{$tableName}` WHERE Column_name = 'phone' AND Non_unique = 0");
        
        if (!empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['phone']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Re-add unique index on phone column
            $table->unique('phone');
        });
    }
};
