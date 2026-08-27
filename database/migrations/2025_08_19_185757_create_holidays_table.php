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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable();
            $table->text('details')->nullable();
            $table->boolean('is_annual')->default(true); // tinyint(1), default 1
            $table->string('tags')->nullable();

            // Foreign key columns
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('thana_id')->nullable()->constrained('thanas')->onDelete('set null');

            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false); // tinyint(1), default 0

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING ---

            // 1. Calendar search
            $table->index(['date', 'status'], 'idx_holiday_calendar');

            // 2. Type lookup
            $table->index(['type', 'status'], 'idx_holiday_type_lookup');

            // 3. Geo filtering
            $table->index(['division_id', 'district_id', 'status'], 'idx_holiday_geo');

            // 4. Featured/Popularity
            $table->index(['is_featured', 'status', 'date'], 'idx_holiday_featured');

            // 5. Audit
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};