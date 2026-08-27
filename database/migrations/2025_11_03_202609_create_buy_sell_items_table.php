<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buy_sell_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('thumbnail')->nullable();
            $table->longText('description')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('buy_sell_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['buy_sell_category_id', 'status', 'is_active', 'published_at'], 'idx_items_listing_main');
            $table->index(['status', 'price', 'is_active'], 'idx_items_price_filter');
            $table->index(['is_featured', 'status', 'published_at'], 'idx_items_featured_feed');
            $table->index(['user_id', 'status', 'created_at'], 'idx_user_items_history');
            $table->index('title');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_items');
    }
};