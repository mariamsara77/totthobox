<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_numbers', function (Blueprint $table) {
            $table->id();

            // Relationships (Using modern shorthand)
            $table->foreignId('contact_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thana_id')->nullable()->constrained()->nullOnDelete();

            // Core Content
            $table->string('unit_name')->nullable();
            $table->string('area')->nullable();
            $table->string('zone')->nullable();
            $table->string('location')->nullable();
            $table->string('name')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('designation')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('address')->nullable();

            // Status & Flags
            $table->string('status')->default('active')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();

            $table->timestamps();
            $table->softDeletes();

            // Optimized Composite Indexes
            $table->index(['contact_category_id', 'division_id', 'district_id', 'status'], 'idx_contact_geo_cat');
            $table->index(['is_featured', 'is_active'], 'idx_contact_featured_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_numbers');
    }
};