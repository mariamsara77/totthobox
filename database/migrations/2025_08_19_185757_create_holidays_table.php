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
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->date('date')->nullable();
            $table->string('type')->nullable(); // যেমন: Public, Religious, National
            $table->text('details')->nullable();
            $table->string('image')->nullable();
            $table->string('tags')->nullable();

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

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. ক্যালেন্ডার বা তারিখ ভিত্তিক দ্রুত সার্চের জন্য
            // WHERE date BETWEEN ? AND ? AND status = 1
            $table->index(['date', 'status'], 'idx_holiday_calendar');

            // ২. ছুটির ধরন অনুযায়ী ফিল্টারিং
            // WHERE type = 'Public' AND status = 1
            $table->index(['type', 'status'], 'idx_holiday_type_lookup');

            // ৩. লোকেশন ভিত্তিক বিশেষ ছুটি (যেমন: আঞ্চলিক ছুটি)
            $table->index(['division_id', 'district_id', 'status'], 'idx_holiday_geo');

            // ৪. পপুলারিটি এবং ফিচারড হলিডে (হোমপেজ স্লাইডার বা লিস্টের জন্য)
            $table->index(['is_featured', 'status', 'date'], 'idx_holiday_featured');

            // ৫. অডিট এবং ট্র্যাকিং
            $table->index(['status', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};