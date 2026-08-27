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
        Schema::create('history_bds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // Laravel 12 style foreign keys
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thana_id')->nullable()->constrained()->nullOnDelete();

            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(0)->index(); // ইনডেক্স এখানেই যোগ করা যায়
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Composite Indexes
            $table->index(['division_id', 'district_id', 'status'], 'idx_history_geo');
            $table->index(['is_featured', 'status'], 'idx_history_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_bds');
    }
};