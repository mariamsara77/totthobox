<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buy_sell_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();

            // Core Product Info
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->text('note')->nullable();

            // Product Classification
            $table->foreignId('buy_sell_category_id')->nullable()
                ->constrained('buy_sell_categories')->onDelete('set null');

            $table->foreignId('buy_sell_item_id')->nullable()
                ->constrained('buy_sell_items')->onDelete('set null');

            // Product Condition
            $table->enum('condition', [
                'new','like_new','used_good','used_fair','refurbished','for_parts'
            ])->default('used_good')->index();

            // Pricing
            $table->decimal('price', 15, 2)->nullable()->index();
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('BDT')->index();
            $table->boolean('is_negotiable')->default(false);

            // Inventory
            $table->string('sku')->nullable()->index();
            $table->integer('stock')->default(1);
            $table->boolean('is_available')->default(true)->index();

            // Location
            $table->foreignId('division_id')->nullable()->constrained('divisions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('thana_id')->nullable()->constrained('thanas')->onDelete('set null');
            $table->string('address')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();

            // Contact Info
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('imo')->nullable();
            $table->string('email')->nullable();

            // Image Count
            $table->unsignedInteger('images_count')->default(0)->index();

            // Flags
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('status', ['draft','pending','published','rejected','archived'])
                ->default('draft')->index();

            // Dates
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Analytics
            $table->unsignedInteger('view_count')->default(0)->index();
            $table->unsignedInteger('favourite_count')->default(0);
            $table->unsignedInteger('share_count')->default(0);

            // Flexible attributes
            $table->json('attributes')->nullable();

            // Audit Fields
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            // Technical
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexing Fix
            $table->index(['is_active', 'status']);
            $table->index(['buy_sell_category_id', 'price']);
            $table->index(['division_id', 'district_id']);
            $table->index(['created_at', 'published_at']);

            // FK for audit trail
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
