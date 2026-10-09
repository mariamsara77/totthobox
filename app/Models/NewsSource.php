<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsSource extends Model
{
    protected $fillable = [
        'source_key',
        'slug',
        'name',
        'language',
        'home_url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    public function headlines(): HasMany
    {
        return $this->hasMany(NewsHeading::class, 'source_key', 'source_key');
    }
}
