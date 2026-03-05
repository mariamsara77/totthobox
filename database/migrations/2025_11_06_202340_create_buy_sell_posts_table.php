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
            $table->string('slug')->unique();
            $table->longText('description')->nullable();
            $table->text('note')->nullable();

            // Product Classification
            $table->foreignId('buy_sell_category_id')->nullable()
                ->constrained('buy_sell_categories')->onDelete('set null');

            $table->foreignId('buy_sell_item_id')->nullable()
                ->constrained('buy_sell_items')->onDelete('set null');

            // Product Condition
            $table->enum('condition', [
                'new','like_new','used_good','used_fair','refurbished','for_parts'
            ])->default('used_good');

            // Pricing
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('discount_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('BDT');
            $table->boolean('is_negotiable')->default(false);

            // Inventory
            $table->string('sku')->nullable();
            $table->integer('stock')->default(1);
            $table->boolean('is_available')->default(true);

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
            $table->unsignedInteger('images_count')->default(0);

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft','pending','published','rejected','archived'])->default('draft');

            // Dates
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Analytics
            $table->unsignedInteger('view_count')->default(0);
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

            // --- ADVANCED COMPOUND INDEXING (Marketplace Optimization) ---

            // ১. হোমপেজ এবং ক্যাটাগরি লিস্টিং ফিল্টার (সবচেয়ে বেশি ব্যবহৃত)
            // এটি WHERE category_id = ? AND status = 'published' AND is_active = 1 ORDER BY published_at DESC কে অপ্টিমাইজ করবে
            $table->index(['buy_sell_category_id', 'status', 'is_active', 'published_at'], 'idx_post_listing_main');

            // ২. লোকেশন ভিত্তিক সার্চ (শহর বা বিভাগ অনুযায়ী ফিল্টার)
            $table->index(['division_id', 'district_id', 'status', 'is_active'], 'idx_post_location_status');

            // ৩. প্রাইস রেঞ্জ ফিল্টার (কম থেকে বেশি বা বেশি থেকে কম দাম)
            $table->index(['status', 'is_active', 'price'], 'idx_post_price_filter');

            // ৪. কন্ডিশন ভিত্তিক ফিল্টার (যেমন: শুধুমাত্র 'New' বা 'Used' পণ্য দেখা)
            $table->index(['condition', 'status', 'is_active'], 'idx_post_condition_filter');

            // ৫. ফিচারড পোস্ট এবং জনপ্রিয় পোস্টের ফিড
            $table->index(['is_featured', 'status', 'view_count'], 'idx_post_featured_popular');

            // ৬. ইউজার প্রোফাইল বা ড্যাশবোর্ড (আমার পোস্টগুলো দেখা)
            $table->index(['user_id', 'status', 'created_at'], 'idx_post_user_history');

            // ৭. এসকিউ (SKU) এবং টাইটেল লুকআপ
            $table->index('sku');
            $table->index('title');

            // ৮. সফট ডিলিট এবং এক্সপায়ার ডেট অপ্টিমাইজেশন
            $table->index('deleted_at');
            $table->index('expires_at');

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