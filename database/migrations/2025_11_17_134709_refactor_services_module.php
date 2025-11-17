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
        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('service_categories', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('description');
            }
            if (!Schema::hasColumn('service_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('services', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable()->after('image');
            }
            if (!Schema::hasColumn('services', 'base_duration')) {
                $table->unsignedInteger('base_duration')->nullable()->after('base_price');
            }
            if (!Schema::hasColumn('services', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('status');
            }
            if (!Schema::hasColumn('services', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('sort_order');
            }
        });

        Schema::table('service_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('service_variants', 'sku')) {
                $table->string('sku', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('service_variants', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('duration');
            }
            if (!Schema::hasColumn('service_variants', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_default');
            }
            if (!Schema::hasColumn('service_variants', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('service_variants', 'notes')) {
                $table->text('notes')->nullable()->after('sort_order');
            }
        });

        Schema::table('variant_attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('variant_attributes', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('combos', function (Blueprint $table) {
            if (!Schema::hasColumn('combos', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('combos', 'image')) {
                $table->string('image', 255)->nullable()->after('description');
            }
            if (!Schema::hasColumn('combos', 'owner_service_id')) {
                $table->foreignId('owner_service_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('services')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('combos', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('status');
            }
        });

        Schema::table('combo_items', function (Blueprint $table) {
            if (!Schema::hasColumn('combo_items', 'service_variant_id')) {
                $table->foreignId('service_variant_id')
                    ->nullable()
                    ->after('service_id')
                    ->constrained('service_variants')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('combo_items', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('service_variant_id');
            }
            if (!Schema::hasColumn('combo_items', 'price_override')) {
                $table->decimal('price_override', 10, 2)->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('combo_items', 'notes')) {
                $table->string('notes', 255)->nullable()->after('price_override');
            }
            if (!Schema::hasColumn('combo_items', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('appointment_details', function (Blueprint $table) {
            if (!Schema::hasColumn('appointment_details', 'combo_id')) {
                $table->foreignId('combo_id')
                    ->nullable()
                    ->after('service_variant_id')
                    ->constrained('combos')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('appointment_details', 'combo_item_id')) {
                $table->foreignId('combo_item_id')
                    ->nullable()
                    ->after('combo_id')
                    ->constrained('combo_items')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('appointment_details', 'notes')) {
                $table->string('notes', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('appointment_details', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_details', function (Blueprint $table) {
            if (Schema::hasColumn('appointment_details', 'created_at')) {
                $table->dropTimestamps();
            }
            if (Schema::hasColumn('appointment_details', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('appointment_details', 'combo_item_id')) {
                $table->dropConstrainedForeignId('combo_item_id');
            }
            if (Schema::hasColumn('appointment_details', 'combo_id')) {
                $table->dropConstrainedForeignId('combo_id');
            }
        });

        Schema::table('combo_items', function (Blueprint $table) {
            if (Schema::hasColumn('combo_items', 'created_at')) {
                $table->dropTimestamps();
            }
            if (Schema::hasColumn('combo_items', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('combo_items', 'price_override')) {
                $table->dropColumn('price_override');
            }
            if (Schema::hasColumn('combo_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('combo_items', 'service_variant_id')) {
                $table->dropConstrainedForeignId('service_variant_id');
            }
        });

        Schema::table('combos', function (Blueprint $table) {
            if (Schema::hasColumn('combos', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('combos', 'owner_service_id')) {
                $table->dropConstrainedForeignId('owner_service_id');
            }
            if (Schema::hasColumn('combos', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('combos', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('variant_attributes', function (Blueprint $table) {
            if (Schema::hasColumn('variant_attributes', 'created_at')) {
                $table->dropTimestamps();
            }
        });

        Schema::table('service_variants', function (Blueprint $table) {
            if (Schema::hasColumn('service_variants', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('service_variants', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('service_variants', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('service_variants', 'is_default')) {
                $table->dropColumn('is_default');
            }
            if (Schema::hasColumn('service_variants', 'sku')) {
                $table->dropColumn('sku');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('services', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('services', 'base_duration')) {
                $table->dropColumn('base_duration');
            }
            if (Schema::hasColumn('services', 'base_price')) {
                $table->dropColumn('base_price');
            }
            if (Schema::hasColumn('services', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('service_categories', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('service_categories', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
