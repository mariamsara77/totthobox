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
            $table->string('image')->nullable();

            // Foreign key columns
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('thana_id')->nullable();

            $table->string('slug')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('status')->default(0);

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
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

            // --- Foreign key constraints ---
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('thana_id')->references('id')->on('thanas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (Performance Optimization) ---

            // ১. স্ট্যাটাস এবং পাবলিশ ডেট অনুযায়ী সর্টিংয়ের জন্য (মিলিয়ন ডাটা থাকলেও রিসেন্ট পোস্ট দ্রুত আসবে)
            $table->index(['status', 'published_at']);

            // ২. লোকেশন ভিত্তিক কম্বাইন্ড সার্চ (বিভাগ > জেলা > থানা > স্ট্যাটাস)
            // এটি WHERE division_id = ? AND status = ? কুয়েরিকে সুপার ফাস্ট করবে
            $table->index(['division_id', 'district_id', 'status'], 'idx_history_location');

            // ৩. ফিচারড কনটেন্ট এবং ভিউ কাউন্ট এনালাইসিসের জন্য
            $table->index(['is_featured', 'status', 'view_count'], 'idx_history_featured_stats');

            // ৪. ইউজার ভিত্তিক হিস্ট্রি ট্র্যাকিং
            $table->index(['user_id', 'status']);
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