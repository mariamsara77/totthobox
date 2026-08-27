<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name_bn')->index();
            $table->string('name_en')->nullable()->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('calorie')->nullable();
            $table->decimal('carb', 8, 2)->unsigned()->nullable();
            $table->decimal('protein', 8, 2)->unsigned()->nullable();
            $table->decimal('fat', 8, 2)->unsigned()->nullable();
            $table->decimal('fiber', 8, 2)->unsigned()->nullable();
            $table->string('serving_size')->nullable();
            $table->foreignId('food_category_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('status')->default(0)->index();
            $table->boolean('is_featured')->default(false)->index();

            $table->timestamps();
            $table->softDeletes();

            // Composite Indexes
            $table->index(['food_category_id', 'status'], 'idx_food_cat_status');
            $table->index(['status', 'calorie'], 'idx_food_cal_filter');
            $table->index(['is_featured', 'status'], 'idx_food_feat_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};