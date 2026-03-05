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
        Schema::create('establishment_bds', function (Blueprint $table) {
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

            // --- ADVANCED INDEXING (Future-Proof Optimization) ---

            // ১. লোকেশন হায়ারার্কি ইনডেক্স: এটি বিভাগ ও জেলা ভিত্তিক সার্চ সুপার ফাস্ট করবে
            // WHERE division_id = ? AND district_id = ? AND status = 1
            $table->index(['division_id', 'district_id', 'status'], 'idx_estab_geo_lookup');

            // ২. ফিচারড এবং পপুলারিটি সর্টিং
            // হোমে বা টপ লিস্টে দেখানোর জন্য: ORDER BY is_featured DESC, view_count DESC
            $table->index(['status', 'is_featured', 'view_count'], 'idx_estab_popular');

            // ৩. পাবলিশ ডেট এবং স্ট্যাটাস (রিসেন্ট প্রতিষ্ঠানের তালিকা)
            $table->index(['status', 'published_at']);

            // ৪. ইউজার এ্যাক্টিভিটি এবং অথর ট্র্যাকিং
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establishment_bds');
    }
};