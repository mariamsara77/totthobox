<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class FoodNutrient extends Pivot
{
    use SoftDeletes;

    protected $table = 'food_nutrients';
    protected $guarded = [];
    public $incrementing = true;

    protected $casts = [
        'amount' => 'float',
    ];

    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function nutrient()
    {
        return $this->belongsTo(Nutrient::class);
    }
}
