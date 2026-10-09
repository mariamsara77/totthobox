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
            $table->string('name', 150);
            $table->string('language', 2)->index();
            $table->string('home_url', 500);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'language', 'position'], 'news_sources_active_order_index');
        });

        // Bootstrap the new catalogue from the existing settings so the first
        // deployment keeps every configured outlet and its previous URL key.
        $now = now();
        $sources = collect(config('news_sources', []))
            ->filter(fn ($source) => is_array($source)
                && ! empty($source['key'])
                && ! empty($source['name'])
                && ! empty($source['language'])
                && ! empty($source['home_url']))
            ->map(fn (array $source) => [
                'source_key' => (string) $source['key'],
                'slug' => (string) ($source['slug'] ?? str_replace('_', '-', $source['key'])),
                'name' => (string) $source['name'],
                'language' => (string) $source['language'],
                'home_url' => (string) $source['home_url'],
                'position' => (int) ($source['order'] ?? 0),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($sources !== []) {
            DB::table('news_sources')->insertOrIgnore($sources);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
