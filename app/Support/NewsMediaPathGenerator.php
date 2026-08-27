<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class NewsMediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // news-thumbs/2026/07/31/{media_id}/
        $date = $media->created_at?->format('Y/m/d') ?? now()->format('Y/m/d');

        return "news-thumbs/{$date}/{$media->id}/";
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive/';
    }
}
