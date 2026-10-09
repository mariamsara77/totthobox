<?php

use App\Models\NewsHeading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

uses(RefreshDatabase::class);

it('returns every configured newspaper even when it has zero headlines', function () {
    $response = $this->getJson('/api/news/sources');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'bn' => [['key', 'slug', 'name', 'language', 'home_url', 'total']],
            'en' => [['key', 'slug', 'name', 'language', 'home_url', 'total']],
        ])
        ->assertJsonPath('bn.0.slug', 'prothom-alo')
        ->assertJsonPath('en.0.slug', 'the-daily-star');

    expect(collect($response->json('bn'))->pluck('key'))
        ->toContain(
            'prothom_alo',
            'kalerkantho',
            'samakal',
            'jugantor',
            'ittefaq',
            'manabzamin',
            'somoy_news',
        );

    expect(collect($response->json('en'))->pluck('key'))
        ->toContain(
            'daily_star',
            'bdnews24',
            'financial_express',
            'new_age',
        );
});

it('uses database source slugs and live headline counts for sidebar links', function () {
    NewsHeading::create([
        'title' => 'Sidebar count headline sample',
        'slug' => 'sidebar-count-headline-sample',
        'source_key' => 'daily-ittefaq', // Legacy key from older scraper records.
        'source_name' => 'Old source label',
        'language' => 'bn',
        'source_link' => 'https://www.ittefaq.com.bd/sidebar-count-sample',
        'image_url' => 'https://images.ittefaq-cdn.example/sample.jpg',
    ]);

    $response = $this->getJson('/api/sidebar/news-sources')
        ->assertOk()
        ->assertJsonStructure([
            'bn' => [['source_key', 'slug', 'source_name', 'language', 'home_url', 'total']],
            'en' => [['source_key', 'slug', 'source_name', 'language', 'home_url', 'total']],
        ]);

    $ittefaq = collect($response->json('bn'))->firstWhere('source_key', 'ittefaq');

    expect($ittefaq)->not->toBeNull()
        ->and($ittefaq['slug'])->toBe('daily-ittefaq')
        ->and($ittefaq['source_name'])->toBe('Daily Ittefaq')
        ->and($ittefaq['total'])->toBe(1);

    $catalog = $this->getJson('/api/news/sources')->assertOk();
    $catalogIttefaq = collect($catalog->json('bn'))->firstWhere('key', 'ittefaq');
    expect($catalogIttefaq['total'])->toBe(1);

    $this->getJson('/api/news?source=ittefaq')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Sidebar count headline sample')
        ->assertJsonPath('data.0.source_slug', 'daily-ittefaq');
});

it('returns only discovery-safe fields and supports source filtering', function () {
    NewsHeading::create([
        'title' => 'Sample headline for filtering',
        'slug' => 'sample-headline-for-filtering',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/sample',
        'image_url' => 'https://www.prothomalo.com/images/sample.jpg',
    ]);

    NewsHeading::create([
        'title' => 'Another outlet headline',
        'slug' => 'another-outlet-headline',
        'source_key' => 'daily_star',
        'source_name' => 'The Daily Star',
        'language' => 'en',
        'source_link' => 'https://www.thedailystar.net/sample',
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Sample headline for filtering')
        ->assertJsonPath('data.0.image_url', 'https://www.prothomalo.com/images/sample.jpg');

    expect($response->json('data.0'))
        ->not->toHaveKey('content')
        ->not->toHaveKey('body')
        ->not->toHaveKey('summary');
});

it('prefers an available local thumbnail over a remote URL', function () {
    Storage::fake('public');
    $path = 'news-images/local-thumbnail.jpg';
    Storage::disk('public')->put($path, 'test-image-data');

    NewsHeading::create([
        'title' => 'Headline with locally stored thumbnail',
        'slug' => 'headline-with-locally-stored-thumbnail',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/local-image-test',
        'image_url' => 'https://img.publisher-cdn.example/image.jpg',
        'local_image_path' => $path,
    ]);

    $expectedUrl = Storage::disk('public')->url($path);

    $this->getJson('/api/news?source=prothom_alo')
        ->assertOk()
        ->assertJsonPath('data.0.image_url', $expectedUrl);
});

it('allows valid CDN thumbnails while keeping article content out of the public feed', function () {
    NewsHeading::create([
        'title' => 'Headline with CDN thumbnail',
        'slug' => 'headline-with-cdn-thumbnail',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/cdn-image-test',
        'image_url' => 'https://img.publisher-cdn.example/image.jpg',
    ]);

    $this->getJson('/api/news?source=prothom_alo')
        ->assertOk()
        ->assertJsonPath('data.0.image_url', 'https://img.publisher-cdn.example/image.jpg');
});

it('normalizes legacy relative thumbnail paths to the backend origin', function () {
    NewsHeading::create([
        'title' => 'Headline with relative thumbnail',
        'slug' => 'headline-with-relative-thumbnail',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/relative-image-test',
        'image_url' => '/storage/news-images/legacy-thumbnail.jpg',
    ]);

    $expectedUrl = rtrim((string) config('app.url'), '/').'/storage/news-images/legacy-thumbnail.jpg';

    $this->getJson('/api/news?source=prothom_alo')
        ->assertOk()
        ->assertJsonPath('data.0.image_url', $expectedUrl);
});

it('returns saved headlines older than seven days unless the visitor chooses a time filter', function () {
    NewsHeading::create([
        'title' => 'Older saved headline should remain visible',
        'slug' => 'older-saved-headline-should-remain-visible',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/old-saved-headline',
        'published_at' => now()->subDays(10),
    ]);

    $this->getJson('/api/news?source=prothom_alo')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Older saved headline should remain visible');

    $this->getJson('/api/news?source=prothom_alo&hours=48')
        ->assertOk()
        ->assertJsonPath('meta.total', 0);
});


it('continues returning headlines when optional enhancement columns are not deployed yet', function () {
    Cache::forget('news_headings_has_column_story_group');
    Cache::forget('news_headings_has_column_local_image_path');

    Schema::table('news_headings', function (Blueprint $table) {
        $table->dropColumn(['story_group', 'local_image_path']);
    });

    NewsHeading::create([
        'title' => 'Headline survives an older production schema',
        'slug' => 'headline-survives-older-production-schema',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/schema-compatibility-test',
    ]);

    $this->getJson('/api/news')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Headline survives an older production schema')
        ->assertJsonPath('data.0.story_group', null)
        ->assertJsonPath('data.0.image_url', null);
});

it('uses the saved creation timestamp when a headline has no publication timestamp', function () {
    $headline = NewsHeading::create([
        'title' => 'Headline without a publisher timestamp',
        'slug' => 'headline-without-publisher-timestamp',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/created-at-fallback-test',
        'published_at' => null,
    ]);

    $this->getJson('/api/news?source=prothom_alo')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Headline without a publisher timestamp')
        ->assertJsonPath('data.0.published_at', $headline->created_at->toIso8601String());
});
