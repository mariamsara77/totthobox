<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FoodCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name_bn','name_en','slug', 'icon', 'description','status','image'];

    public function foods()
    {
        return $this->hasMany(Food::class);
    }

    // Auto-generate slug on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($foodCategory) {
            if (empty($foodCategory->slug)) {
                $foodCategory->slug = Str::slug($foodCategory->name_en) . '-' . Str::random(5);
            }
        });
    }
}
