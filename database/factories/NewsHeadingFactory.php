<?php

namespace Database\Factories;

use App\Models\NewsHeading;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsHeading>
 */
class NewsHeadingFactory extends Factory
{
    protected $model = NewsHeading::class;

    public function definition(): array
    {
        $title = fake()->sentence();
        $url = fake()->unique()->url().'/'.Str::slug($title);

        return [
            'title' => $title,
            'summary' => null,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(8)),
            'source_link' => $url,
            'source_hash' => hash('sha256', $url),
            'source_name' => 'Prothom Alo',
            'source_key' => 'prothom_alo',
            'category' => 'National',
            'story_group' => null,
            'image_url' => null,
            'language' => 'bn',
            'published_at' => now()->subMinutes(fake()->numberBetween(0, 1000)),
            'local_image_path' => null,
        ];
    }
}
