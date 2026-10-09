<?php

use App\Models\NewsHeading;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        'source_key' => 'ittefaq',
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
