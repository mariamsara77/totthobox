<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sura extends Model
{
    use HasFactory;

    protected $fillable = [
        'sura_no',
        'name_arabic',
        'name_english',
        'name_bangla',
        'meaning_bangla',
        'revelation_type',
        'total_ayat',
        'slug',
        'is_active',
        'is_featured'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    // এক সূরায় অনেক আয়াত থাকে
    public function ayats(): HasMany
    {
        return $this->hasMany(Quran::class, 'sura_id')->orderBy('ayat_no', 'asc');
    }

    // Scopes for filtering
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    public function scopeMeccan($query)
    {
        return $query->where('revelation_type', 'Meccan');
    }
}