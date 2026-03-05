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
        Schema::create('contact_numbers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contact_category_id')->nullable()->constrained('contact_categories')->nullOnDelete();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('thana_id')->nullable();

            $table->string('unit_name')->nullable();
            $table->string('area')->nullable();
            $table->string('zone')->nullable();
            $table->string('location')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('type')->nullable();
            $table->string('designation')->nullable();
            $table->string('alt_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->default('active');

            $table->json('extra_attributes')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->integer('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('thana_id')->references('id')->on('thanas')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (Optimized for Search) ---

            // ১. ক্যাটাগরি এবং লোকেশন ভিত্তিক ফিল্টারিং (সবচেয়ে বেশি ব্যবহৃত কোয়েরি)
            // WHERE contact_category_id = ? AND division_id = ? AND status = 'active'
            $table->index(['contact_category_id', 'division_id', 'district_id', 'status'], 'idx_contact_geo_cat');

            // ২. ফোন নম্বর দিয়ে দ্রুত সার্চ করার জন্য
            $table->index('phone');

            // ৩. নাম এবং ডেজিগনেশন দিয়ে সার্চের জন্য
            $table->index(['name', 'status']);

            // ৪. ফিচারড এবং পপুলারিটি (ভিউ কাউন্ট) এর জন্য
            $table->index(['is_featured', 'is_active', 'view_count'], 'idx_contact_featured_stats');

            // ৫. ইমেইল ইনডেক্স
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_numbers');
    }
};