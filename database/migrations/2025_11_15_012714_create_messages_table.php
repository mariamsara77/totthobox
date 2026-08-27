<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();

            $table->text('message')->nullable();
            $table->json('meta')->nullable();

            // Status fields
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->string('attachment')->nullable();
            $table->string('attachment_type', 50)->nullable();

            $table->foreignId('parent_id')->nullable()->constrained('messages')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // --- ADVANCED INDEXING (Real-time Performance) ---

            // ১. চ্যাট উইন্ডো অপ্টিমাইজেশন (conversation লোড করার জন্য সবচাইতে গুরুত্বপূর্ণ)
            // এটি WHERE (sender_id = A AND receiver_id = B) ORDER BY created_at কোডকে কয়েকগুণ দ্রুত করবে।
            $table->index(['sender_id', 'receiver_id', 'created_at'], 'idx_chat_history_direct');
            $table->index(['receiver_id', 'sender_id', 'created_at'], 'idx_chat_history_reverse');

            // ২. আনরিড মেসেজ কাউন্ট এবং নোটিফিকেশন (সবচেয়ে বেশি ব্যবহৃত কোয়েরি)
            // এটি SELECT count(*) FROM messages WHERE receiver_id = ? AND read = 0 কোয়েরিকে সুপার ফাস্ট করবে।
            $table->index(['receiver_id', 'read', 'created_at'], 'idx_unread_counter');

            // ৩. সফট ডিলিট এবং প্যারেন্ট মেসেজ (রিপ্লাই সিস্টেম) এর জন্য
            $table->index(['parent_id', 'deleted_at']);

            // ৪. টাইমস্ট্যাম্প সর্টিং (ইনবক্স লিস্ট বা রিসেন্ট চ্যাট দেখানোর জন্য)
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};