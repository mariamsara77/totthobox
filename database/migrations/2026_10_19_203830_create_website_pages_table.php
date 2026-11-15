<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_pages', function (Blueprint $table) {
            $table->id();

            // মূল ইউআরএল (ইউনিক)
            $table->string('url', 500)->unique();

            // পেজের টাইটেল
            $table->string('title')->nullable();

            // ক্লিন করা পুরো কনটেন্ট
            $table->longText('content')->nullable();

            // ইমেজ গুলো JSON আকারে
            // উদাহরণ: [{"url": "https://...", "alt": "..."}]
            $table->json('images')->nullable();

            // অতিরিক্ত মেটা ডেটা (description, keywords ইত্যাদি)
            $table->json('meta')->nullable();

            // কনটেন্ট পরিবর্তন detect করার জন্য
            $table->string('content_hash', 32)->nullable()->index();

            // শেষ কখন ক্রল হয়েছে
            $table->timestamp('last_crawled_at')->nullable()->index();

            // ভবিষ্যতে Meilisearch / Scout এর জন্য স্ট্যাটাস
            $table->boolean('is_indexed')->default(false)->index();

            $table->timestamps();

            // সার্চের জন্য ইনডেক্স (MySQL FULLTEXT)
            // PostgreSQL ব্যবহার করলে পরে tsvector এ কনভার্ট করা যাবে
            $table->fullText(['title', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_pages');
    }
};