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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('class_level_id')->constrained();
            $table->text('question_text');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->integer('marks')->default(1);
            $table->integer('difficulty_level')->default(1);
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            // Other fields
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Timestamps & soft deletes
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Optimized for Large Data) ---

            // ১. নির্দিষ্ট ক্লাস এবং সাবজেক্ট অনুযায়ী প্রশ্নের তালিকা দ্রুত পাওয়ার জন্য (সবচেয়ে গুরুত্বপূর্ণ)
            // এটি WHERE class_level_id = ? AND subject_id = ? AND is_active = 1 কোয়েরিকে অপ্টিমাইজ করবে
            $table->index(['class_level_id', 'subject_id', 'is_active'], 'idx_ques_class_sub_active');

            // ২. ডিফিকাল্টি লেভেল এবং মার্কস অনুযায়ী ফিল্টারিং
            // কুইজ জেনারেটর বা ফিল্টারিং পেজের জন্য: WHERE difficulty_level = ? AND is_active = 1
            $table->index(['difficulty_level', 'is_active'], 'idx_ques_difficulty');

            // ৩. স্ট্যাটাস এবং ফিচারড সর্টিং (টপ বা রেন্ডম প্রশ্ন দেখানোর জন্য)
            $table->index(['is_active', 'is_featured', 'view_count'], 'idx_ques_stats');

            // ৪. টাইমস্ট্যাম্প এবং সফট ডিলিট অপ্টিমাইজেশন
            $table->index(['deleted_at', 'published_at']);

            // ৫. ইউজার আইডেন্টিটি ট্র্যাকিং
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};