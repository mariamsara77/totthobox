<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_headings', function (Blueprint $table) {
            // ★ story_group: একই ঘটনা/খবর একাধিক সোর্সে প্রকাশ হলে সবাইকে একই
            // group-id-তে বাঁধা হয় — এই ভিত্তিতেই "ক্রস-সোর্স কভারেজ" ফিচার কাজ করে।
            $table->string('story_group', 40)->nullable()->index()->after('category');

            // ★ summary: সম্পূর্ণ ঐচ্ছিক, কিন্তু এখানেই আপনার/এডিটরের নিজের হাতে
            // লেখা ২-৩ বাক্যের সারাংশ বসবে (সোর্স থেকে কপি না করে)। এটা খালি
            // থাকলে UI-তে দেখানো হবে না, ভাঙবে না।
            $table->text('summary')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('news_headings', function (Blueprint $table) {
            $table->dropColumn(['story_group', 'summary']);
        });
    }
};
