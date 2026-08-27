<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends BaseModel
{
    protected $fillable = ['title'];

    public function roleHistories(): HasMany
    {
        return $this->hasMany(RoleHistory::class);
    }
}