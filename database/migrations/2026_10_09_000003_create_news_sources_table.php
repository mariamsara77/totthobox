<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('source_key', 64)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 160);
            $table->string('language', 2)->index();
            $table->string('home_url', 2048);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // This is the initial source catalogue. Source names/slugs/order are now
        // read from this table by APIs and the frontend instead of being copied
        // into separate hardcoded sidebar maps.
        $now = now();
        DB::table('news_sources')->insert([
            ['source_key' => 'prothom_alo', 'slug' => 'prothom-alo', 'name' => 'Prothom Alo', 'language' => 'bn', 'home_url' => 'https://www.prothomalo.com/', 'position' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'kalerkantho', 'slug' => 'kaler-kantho', 'name' => 'Kaler Kantho', 'language' => 'bn', 'home_url' => 'https://www.kalerkantho.com/', 'position' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'samakal', 'slug' => 'samakal', 'name' => 'Samakal', 'language' => 'bn', 'home_url' => 'https://samakal.com/', 'position' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'jugantor', 'slug' => 'jugantor', 'name' => 'Jugantor', 'language' => 'bn', 'home_url' => 'https://www.jugantor.com/', 'position' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'ittefaq', 'slug' => 'daily-ittefaq', 'name' => 'Daily Ittefaq', 'language' => 'bn', 'home_url' => 'https://www.ittefaq.com.bd/', 'position' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'manabzamin', 'slug' => 'manabzamin', 'name' => 'Manabzamin', 'language' => 'bn', 'home_url' => 'https://www.mzamin.com/', 'position' => 60, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'somoy_news', 'slug' => 'somoy-news', 'name' => 'Somoy News', 'language' => 'bn', 'home_url' => 'https://www.somoynews.tv/', 'position' => 70, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'daily_star', 'slug' => 'the-daily-star', 'name' => 'The Daily Star', 'language' => 'en', 'home_url' => 'https://www.thedailystar.net/', 'position' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'bdnews24', 'slug' => 'bdnews24', 'name' => 'bdnews24', 'language' => 'en', 'home_url' => 'https://bdnews24.com/', 'position' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'financial_express', 'slug' => 'the-financial-express', 'name' => 'The Financial Express', 'language' => 'en', 'home_url' => 'https://thefinancialexpress.com.bd/', 'position' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['source_key' => 'new_age', 'slug' => 'new-age', 'name' => 'New Age', 'language' => 'en', 'home_url' => 'https://www.newagebd.net/', 'position' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
