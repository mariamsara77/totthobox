<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // ১. pages টেবিল তৈরি
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500);
            $table->char('url_hash', 40)->unique();
            $table->string('title', 255)->nullable();
            $table->string('route_name', 100)->nullable()->index();
            $table->timestamps();
        });

        // ২. page_views-এ page_id কলাম যোগ
        Schema::table('page_views', function (Blueprint $table) {
            $table->foreignId('page_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('pages')
                  ->nullOnDelete();
        });

        // ৩. পুরনো ডাটা থেকে unique page তৈরি + page_id আপডেট
        // (বড় টেবিল হলে chunk করে করা ভালো)
        DB::table('page_views')
            ->select('id', 'url', 'url_hash', 'title', 'route_name')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    if (empty($row->url_hash) && !empty($row->url)) {
                        $row->url_hash = sha1($row->url);
                    }

                    if (empty($row->url_hash)) {
                        continue;
                    }

                    // page তৈরি বা খুঁজে বের করা
                    $pageId = DB::table('pages')->where('url_hash', $row->url_hash)->value('id');

                    if (!$pageId) {
                        $pageId = DB::table('pages')->insertGetId([
                            'url'        => Str::limit($row->url ?? '', 500),
                            'url_hash'   => $row->url_hash,
                            'title'      => $row->title,
                            'route_name' => $row->route_name,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // page_views আপডেট
                    DB::table('page_views')
                        ->where('id', $row->id)
                        ->update(['page_id' => $pageId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropConstrainedForeignId('page_id');
        });

        Schema::dropIfExists('pages');
    }
};