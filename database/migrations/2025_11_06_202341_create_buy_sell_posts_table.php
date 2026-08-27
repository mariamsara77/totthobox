<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buy_sell_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('buy_sell_category_id')->nullable()
                ->constrained('buy_sell_categories')->onDelete('set null');
            $table->foreignId('buy_sell_item_id')->nullable()
                ->constrained('buy_sell_items')->onDelete('set null');
            $table->enum('condition', [
                'new',
                'like_new',
                'used_good',
                'used_fair',
                'refurbished',
                'for_parts'
            ])->default('used_good');
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->boolean('is_negotiable')->default(false);
            $table->string('sku')->nullable();
            $table->integer('stock')->default(1);
            $table->boolean('is_available')->default(true);
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('thana_id')->nullable()->constrained('thanas')->onDelete('set null');
            $table->string('address')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('imo')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'pending', 'published', 'rejected', 'archived'])->default('draft');

            // Dates
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('favourite_count')->default(0);
            $table->json('attributes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['buy_sell_category_id', 'status', 'is_active', 'published_at'], 'idx_post_listing_main');
            $table->index(['division_id', 'district_id', 'status', 'is_active'], 'idx_post_location_status');
            $table->index(['status', 'is_active', 'price'], 'idx_post_price_filter');
            $table->index(['condition', 'status', 'is_active'], 'idx_post_condition_filter');
            $table->index(['is_featured', 'status'], 'idx_post_featured_popular');
            $table->index(['user_id', 'status', 'created_at'], 'idx_post_user_history');
            $table->index('sku');
            $table->index('title');
            $table->index('deleted_at');
            $table->index('expires_at');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_posts');
    }
};