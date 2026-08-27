<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = [
        'url',
        'url_hash',
        'title',
        'route_name',
    ];

    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }
}