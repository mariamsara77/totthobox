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
        Schema::create('tourism_bds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('map')->nullable();

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

            // --- ADVANCED INDEXING (Optimization for Millions of Rows) ---

            // ১. লোকেশন ফিল্টারিং: বিভাগ, জেলা এবং থানা অনুযায়ী সার্চ দ্রুত করার জন্য
            // WHERE division_id = ? AND status = 1
            $table->index(['division_id', 'district_id', 'status'], 'idx_tourism_geo_lookup');

            // ২. পপুলারিটি এবং ফিচারড সর্টিং (হোমপেজ এবং টপ চার্ট এর জন্য)
            // ORDER BY is_featured DESC, view_count DESC
            $table->index(['status', 'is_featured', 'view_count'], 'idx_tourism_popularity');

            // ৩. স্লাগ এবং স্ট্যাটাস কম্বিনেশন (পাবলিকলি পেজ দেখানোর জন্য)
            // WHERE slug = ? AND status = 1
            $table->index(['slug', 'status']);

            // ৪. পাবলিশ ডেট অনুযায়ী সর্টিং (রিসেন্ট ট্যুরিস্ট স্পট)
            $table->index(['status', 'published_at']);

            // ৫. ইউজার একটিভিটি ট্র্যাকিং
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourism_bds');
    }
};