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
        Schema::create('class_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['is_active', 'order'], 'idx_class_active_order');
            $table->index(['is_featured', 'status']);
            $table->index(['deleted_at', 'is_active']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('class_levels');
    }
};