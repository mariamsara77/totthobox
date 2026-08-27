<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intro_bds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('intro_category')->nullable();
            $table->longText('description')->nullable();

            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('thana_id')->nullable();

            $table->string('slug');
            $table->tinyInteger('status')->default(0);

            // Ordering Fields
            $table->integer('sort_order')->default(0);
            $table->integer('featured_order')->default(0);
            $table->integer('category_order')->default(0);

            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // --- FOREIGN KEYS (No changes needed in model) ---
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('thana_id')->references('id')->on('thanas')->onDelete('set null');
            $table->index('slug');
            $table->index('status');
            $table->index(['division_id', 'district_id', 'status'], 'idx_location_active');

            $table->index(['intro_category', 'status', 'sort_order'], 'idx_cat_sort_status');

            $table->index(['is_featured', 'status', 'featured_order'], 'idx_featured_display');

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intro_bds');
    }
};