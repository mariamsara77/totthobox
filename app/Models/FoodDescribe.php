<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodDescribe extends Model
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
     * Auto-generate slug from English name (or Bangla if English missing).
     */
    protected static function booted()
    {
        static::creating(function ($food) {
            if (empty($food->slug)) {
                $base = $food->english_name ?: $food->bangla_name;
                $food->slug = Str::slug($base);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }


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
