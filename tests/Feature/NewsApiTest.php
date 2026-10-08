<?php

use App\Models\NewsHeading;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
});

it('returns only discovery-safe fields and supports source filtering', function () {
    NewsHeading::factory()->create([
        'title' => 'Sample headline for filtering',
        'source_key' => 'prothom_alo',
        'source_name' => 'Prothom Alo',
        'language' => 'bn',
        'source_link' => 'https://www.prothomalo.com/sample',
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
        ->assertJsonPath('data.0.title', 'Sample headline for filtering');

    expect($response->json('data.0'))
        ->not->toHaveKey('content')
        ->not->toHaveKey('body')
        ->not->toHaveKey('summary')
        ->not->toHaveKey('image_url');
});
