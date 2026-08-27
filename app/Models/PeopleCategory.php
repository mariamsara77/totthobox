<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PeopleCategory extends BaseModel
{
    protected $fillable = ['name', 'slug'];

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'category_person', 'category_id', 'person_id');
    }
}