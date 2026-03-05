<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('intro_bds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('intro_category')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();

            // Foreign Key Columns (BigInteger for relationship consistency)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('thana_id')->nullable();

            $table->string('slug');
            $table->tinyInteger('status')->default(0);

            // Ordering Fields
            $table->integer('sort_order')->default(0);
            $table->integer('featured_order')->default(0);
            $table->integer('category_order')->default(0);

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // --- FOREIGN KEYS (No changes needed in model) ---
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('thana_id')->references('id')->on('thanas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (The Magic Part) ---

            // ১. বেসিক ইউনিক সার্চ
            $table->index('slug');
            $table->index('status');

            // ২. অ্যাডভান্সড লোকেশন ফিল্টারিং (মিলিয়ন ডাটা থাকলেও ১ সেকেন্ডে রেজাল্ট আসবে)
            // এটি WHERE division_id = ? AND district_id = ? AND status = ? কোয়েরিকে ফাস্ট করবে
            $table->index(['division_id', 'district_id', 'status'], 'idx_location_active');

            // ৩. ক্যাটাগরি এবং সর্টিং এর জন্য (Listing page optimization)
            $table->index(['intro_category', 'status', 'sort_order'], 'idx_cat_sort_status');

            // ৪. হোমপেজ বা ফিচারড সেকশনের জন্য
            $table->index(['is_featured', 'status', 'featured_order'], 'idx_featured_display');

            // ৫. পাবলিশ ডেট অনুযায়ী আর্কিভ বা রিসেন্ট পোস্টের জন্য
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intro_bds');
    }
};