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
        Schema::create('ministers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('designation')->nullable();
            $table->string('rank')->nullable();
            $table->string('party')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('bio')->nullable();

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

            // --- ADVANCED INDEXING (Maximum Performance) ---

            // ১. বর্তমান মন্ত্রীদের দ্রুত খুঁজে পাওয়ার জন্য (সবচেয়ে বেশি ব্যবহৃত হবে)
            // WHERE is_current = 1 AND status = 1
            $table->index(['is_current', 'status'], 'idx_ministers_current');

            // ২. পদবী এবং এলাকা ভিত্তিক যৌথ ইনডেক্স
            // WHERE designation = 'Education Minister' AND status = 1
            $table->index(['designation', 'status'], 'idx_ministers_office');

            // ৩. এলাকা ভিত্তিক মন্ত্রী বা প্রাক্তন মন্ত্রীদের ফিল্টারিং
            // WHERE division_id = ? AND status = 1
            $table->index(['division_id', 'district_id', 'status'], 'idx_ministers_geo');

            // ৪. পপুলারিটি এবং স্লাগ ইনডেক্স
            $table->index(['status', 'is_featured', 'view_count'], 'idx_ministers_featured_stats');

            // ৫. সময়কাল ভিত্তিক সার্চ (প্রাক্তন মন্ত্রী খুঁজার জন্য)
            $table->index(['from_date', 'to_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ministers');
    }
};