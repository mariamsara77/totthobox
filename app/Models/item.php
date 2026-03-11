<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class item extends BaseModel
{
    protected $fillable = ['title', 'body', 'meta'];
    protected $casts = [
        'meta' => 'array'
    ];
}
