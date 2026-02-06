<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
     protected $fillable = [
        'reported_by',
        'target_type',
        'target_id',
        'reason',
        'details',
        'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    // যেকোনো model এইটার target হতে পারে
    public function target()
    {
        return $this->morphTo();
    }
}
