<?php

use App\\Models\\NewsHeading;
use App\\Models\\NewsSource;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Storage;

uses(RefreshDatabase::class);

it('returns every configured newspaper even when it has zero headlines', function () {
    $response = $this->getJson('/api/news/sources');

    $response
        ->assertOk()
        ->assertJsonStructure([
            'bn',
            'en',
        ]);

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

    expect(collect($response->json('bn'))->firstWhere('key', 'prothom_alo')['slug'])
        ->toBe('prothom-alo');

    $sidebar = $this->getJson('/api/sidebar/news-sources')->assertOk();
    expect(collect($sidebar->json('bn'))->firstWhere('source_key', 'prothom_alo')['slug'])
        ->toBe('prothom-alo');
});


it('uses database-managed slugs in both the news API and sidebar menu API', function () {
    NewsSource::query()
        ->where('source_key', 'prothom_alo')
        ->firstOrFail()
        ->update(['slug' => 'prothom-alo-latest']);

    $newsSources = $this->getJson('/api/news/sources')->assertOk()->json();
    $sidebarSources = $this->getJson('/api/sidebar/news-sources')->assertOk()->json();

    expect(collect($newsSources['bn'])->firstWhere('key', 'prothom_alo')['slug'])
        ->toBe('prothom-alo-latest');

    expect(collect($sidebarSources['bn'])->firstWhere('source_key', 'prothom_alo')['slug'])
        ->toBe('prothom-alo-latest');

    NewsHeading::factory()->create([
        'title' => 'Database slug headline',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/database-slug-headline',
    ]);

    $news = $this->getJson('/api/news?source=prothom_alo')->assertOk();

    expect($news->json('data.0.source_slug'))->toBe('prothom-alo-latest');
});

it('returns discovery-safe fields, images and source slugs for the news frontend', function () {
    NewsHeading::factory()->create([
        'title' => 'Sample headline for filtering',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/sample',
        'image_url' => 'https://images.example.com/sample.jpg',
    ]);

    NewsHeading::factory()->create([
        'title' => 'Another outlet headline',
        'source_key' => 'daily_star',
        'source_name' => 'The Daily Star',
        'language' => 'en',
        'source_link' => 'https://www.thedailystar.net/sample',
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('data.0.title', 'Sample headline for filtering')
        ->assertJsonPath('data.0.image_url', 'https://images.example.com/sample.jpg')
        ->assertJsonPath('data.0.source_slug', 'prothom-alo');

    expect($response->json('data.0'))
        ->not->toHaveKey('content')
        ->not->toHaveKey('body')
        ->not->toHaveKey('summary')
        ->not->toHaveKey('local_image_path');
});

it('prefers a managed local thumbnail when a remote publisher image is also present', function () {
    Storage::fake('public');
    Storage::disk('public')->put('news-thumbs/preferred.jpg', 'sample-image');

    NewsHeading::factory()->create([
        'title' => 'Prefer local image news item',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/local-preferred-sample',
        'image_url' => 'https://images.example.com/expired-or-hotlink-protected.jpg',
        'local_image_path' => 'news-thumbs/preferred.jpg',
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10');

    $response->assertOk();

    expect($response->json('data.0.image_url'))
        ->toEndWith('/storage/news-thumbs/preferred.jpg');
});

it('resolves managed local news thumbnails into public storage URLs', function () {
    Storage::fake('public');
    Storage::disk('public')->put('news-thumbs/sample.jpg', 'sample-image');

    NewsHeading::factory()->create([
        'title' => 'Local thumbnail news item',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/local-sample',
        'image_url' => null,
        'local_image_path' => 'news-thumbs/sample.jpg',
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10');

    $response->assertOk();

    expect($response->json('data.0.image_url'))
        ->toEndWith('/storage/news-thumbs/sample.jpg');
});
