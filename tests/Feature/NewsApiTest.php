<?php

use App\Models\NewsHeading;
use App\Models\NewsSource;
use App\Services\NewsScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

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


it('prefers a managed local thumbnail and includes the publisher URL as fallback', function () {
    Storage::fake('public');
    Storage::disk('public')->put('news-thumbs/local-first.jpg', 'sample-image');

    NewsHeading::factory()->create([
        'title' => 'Local preferred news item',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/local-first-sample',
        'image_url' => 'https://images.example.com/local-first.jpg',
        'local_image_path' => 'news-thumbs/local-first.jpg',
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10')->assertOk();

    expect($response->json('data.0.image_url'))
        ->toEndWith('/storage/news-thumbs/local-first.jpg')
        ->and($response->json('data.0.image_fallback_url'))
        ->toBe('https://images.example.com/local-first.jpg');
});


it('resolves root-relative publisher image URLs for legacy RSS headlines', function () {
    NewsHeading::factory()->create([
        'title' => 'Legacy relative image headline',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/bangladesh/relative-image',
        'image_url' => '/media/news/relative-image.webp',
        'local_image_path' => null,
    ]);

    $response = $this->getJson('/api/news?source=prothom_alo&per_page=10')->assertOk();

    expect($response->json('data.0.image_url'))
        ->toBe('https://www.prothomalo.com/media/news/relative-image.webp');
});

it('backfills missing images on existing headlines without promoting them', function () {
    $link = 'https://www.prothomalo.com/bangladesh/existing-image-backfill';

    $headline = NewsHeading::factory()->create([
        'title' => 'Existing headline without an image',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => $link,
        'source_hash' => hash('sha256', $link),
        'image_url' => null,
        'local_image_path' => null,
        'published_at' => now()->subDays(2),
    ]);

    $service = new class extends NewsScraperService {
        public function storeHeadline(array $source, array $data): bool
        {
            return $this->persistNews($source, $data);
        }
    };

    $created = $service->storeHeadline(
        ['key' => 'prothom_alo', 'name' => 'Prothom Alo', 'language' => 'bn'],
        [
            'title' => 'Existing headline without an image',
            'link' => $link,
            'image' => '/media/news/existing-image.webp',
            'date' => now(),
            'category' => 'National',
        ]
    );

    $headline->refresh();

    expect($created)->toBeFalse()
        ->and($headline->image_url)->toBe('https://www.prothomalo.com/media/news/existing-image.webp')
        ->and($headline->published_at->toDateString())->toBe(now()->subDays(2)->toDateString());
});

 
it('uses a bounded Open Graph image fallback to repair existing image-less headlines', function () {
    $link = 'https://www.prothomalo.com/bangladesh/og-image-backfill';

    $headline = NewsHeading::factory()->create([
        'title' => 'Existing headline missing image',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => $link,
        'source_hash' => hash('sha256', $link),
        'image_url' => null,
        'local_image_path' => null,
        'published_at' => now()->subDays(3),
    ]);

    $service = new class extends NewsScraperService {
        public function storeHeadline(array $source, array $data): bool
        {
            return $this->persistNews($source, $data);
        }

        protected function getMetaImage(string $url): ?string
        {
            return 'https://images.example.com/recovered-og-image.jpg';
        }
    };

    $created = $service->storeHeadline(
        ['key' => 'prothom_alo', 'name' => 'Prothom Alo', 'language' => 'bn'],
        [
            'title' => 'Existing headline missing image',
            'link' => $link,
            'image' => null,
            'date' => now(),
            'category' => 'National',
        ]
    );

    $headline->refresh();

    expect($created)->toBeFalse()
        ->and($headline->image_url)->toBe('https://images.example.com/recovered-og-image.jpg')
        ->and($headline->published_at->toDateString())->toBe(now()->subDays(3)->toDateString());
});
