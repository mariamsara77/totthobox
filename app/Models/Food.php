<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'foods';

    protected $fillable = [
        'name_bn','name_en','slug','description',
        'calorie','carb','protein','fat','fiber','serving_size',
        'food_category_id','status','image',
        'meta_title','meta_description','meta_keywords',
        'user_id','created_by','updated_by','deleted_by','published_by',
        'published_at','view_count','is_featured','ip_address','user_agent'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    // Relations
    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'food_category_id');
    }

    public function nutrients()
    {
        return $this->belongsToMany(Nutrient::class, 'food_nutrients')
                    ->using(FoodNutrient::class)   // custom pivot model
                    ->withPivot('amount')
                    ->withTimestamps();
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
    public function scopeSearch($query, $term)
    {
        $term = "%$term%";
        return $query->where('name_bn', 'like', $term)
                     ->orWhere('name_en', 'like', $term)
                     ->orWhere('description', 'like', $term);
    }
    public function vitamins()
    {
        return $this->belongsToMany(Vitamin::class, 'food_vitamins')
                    ->using(FoodVitamin::class)   // custom pivot model
                    ->withPivot('amount')
                    ->withTimestamps();
    }
    
}
