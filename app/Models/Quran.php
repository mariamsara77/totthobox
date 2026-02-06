<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Quran extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'sura_id',
        'para_id',
        'ayat_no',
        'text_arabic',
        'text_bangla',
        'text_english',
        'audio_url',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // আয়াতে অবশ্যই একটি সূরা থাকবে
    public function sura(): BelongsTo
    {
        return $this->belongsTo(Sura::class);
    }

    // আয়াতে অবশ্যই একটি পারা থাকবে
    public function para(): BelongsTo
    {
        return $this->belongsTo(Para::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}