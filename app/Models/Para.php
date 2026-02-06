<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Para extends Model
{
    use HasFactory;

    protected $fillable = [
        'para_number',
        'name_arabic',
        'name_english',
        'name_bangla',
        'slug',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // এক পারায় অনেক আয়াত থাকে
    public function ayats(): HasMany
    {
        return $this->hasMany(Quran::class, 'para_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}