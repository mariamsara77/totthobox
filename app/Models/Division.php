<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'user_id',
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
