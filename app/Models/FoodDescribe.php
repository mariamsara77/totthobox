<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FoodDescribe extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'food_describes';

    protected $fillable = [
        'bangla_name',
        'english_name',
        'category',
        'sub_category',
        'description',
        'health_benefits',
        'nutrients',
        'medical_info',
        'combinations',
        'others',
        'Benefits',
        'References',
        'image',
        'slug',
    ];

    /**
     * Scope for filtering by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for search (Bangla or English name)
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('bangla_name', 'like', "%{$term}%")
            ->orWhere('english_name', 'like', "%{$term}%");
    }
}
