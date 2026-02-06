<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nutrient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_bn','name_en','slug','unit','description',
        'status','image','meta_title','meta_description','meta_keywords',
        'user_id','created_by','updated_by','deleted_by','published_by',
        'published_at','view_count','is_featured','ip_address','user_agent'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function foods()
    {
        return $this->belongsToMany(Food::class, 'food_nutrients')
                    ->withPivot('amount','note')
                    ->withTimestamps()
                    ->using(FoodNutrient::class);
    }
}
