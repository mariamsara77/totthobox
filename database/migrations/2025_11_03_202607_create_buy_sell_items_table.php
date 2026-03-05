<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buy_sell_items', function (Blueprint $table) {
            $table->id();

            // Core Data
            $table->string('title');
            $table->string('slug')->unique(); // Unique এমনিতেই ইনডেক্স তৈরি করে
            $table->string('thumbnail')->nullable();
            $table->longText('description')->nullable();
            $table->text('note')->nullable();

            // Relational Mapping
            $table->foreignId('buy_sell_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Publication & Status Control
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();

            // Pricing & Analytics
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedBigInteger('view_count')->default(0);

            // SEO Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            // Tech Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Time Tracking
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED COMPOUND INDEXES (Performance Boost) ---

            // ১. ফ্রন্টএন্ড লিস্টিং এবং ফিল্টারিং এর জন্য (সবচেয়ে গুরুত্বপূর্ণ)
            // এটি ক্যাটাগরি পেজে পাবলিশড আইটেমগুলো দ্রুত লোড করবে
            $table->index(['buy_sell_category_id', 'status', 'is_active', 'published_at'], 'idx_items_listing_main');

            // ২. প্রাইস ফিল্টারিং এবং সর্টিং এর জন্য
            // ইউজার যখন কম থেকে বেশি দামের আইটেম ফিল্টার করবে
            $table->index(['status', 'price', 'is_active'], 'idx_items_price_filter');

            // ৩. লিডারবোর্ড বা পপুলার আইটেমের জন্য
            // WHERE status = 'published' ORDER BY view_count DESC
            $table->index(['status', 'view_count', 'is_active'], 'idx_items_popular');

            // ৪. হোমপেজ ফিচারড সেকশন এবং সর্টিং
            $table->index(['is_featured', 'status', 'published_at'], 'idx_items_featured_feed');

            // ৫. ইউজার ড্যাশবোর্ডের জন্য (আমার বিজ্ঞাপনগুলো দেখতে)
            $table->index(['user_id', 'status', 'created_at'], 'idx_user_items_history');

            // ৬. টাইটেল সার্চ অপ্টিমাইজেশন
            $table->index('title');

            // ৭. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_items');
    }
};