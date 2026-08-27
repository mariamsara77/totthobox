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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // --- ADVANCED INDEXING (System Performance) ---

            // ১. আনরিড নোটিফিকেশন দ্রুত লোড ও কাউন্ট করার জন্য (সবচেয়ে গুরুত্বপূর্ণ)
            // লারাভেল ডিফল্টভাবে [notifiable_id, notifiable_type] ইনডেক্স দেয়, 
            // আমরা সেটিকে read_at এর সাথে কম্বাইন করছি।
            $table->index(['notifiable_id', 'notifiable_type', 'read_at'], 'idx_notifications_unread_lookup');

            // ২. নির্দিষ্ট টাইপের নোটিফিকেশন ফিল্টার করার জন্য (যেমন: শুধু 'Message' বা 'Order' নোটিফিকেশন)
            $table->index(['notifiable_id', 'notifiable_type', 'type'], 'idx_notifications_type_lookup');

            // ৩. টাইমস্ট্যাম্প সর্টিং (সবচেয়ে নতুন নোটিফিকেশনগুলো আগে দেখানোর জন্য)
            $table->index(['notifiable_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};