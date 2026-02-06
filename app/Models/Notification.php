<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];
    
    
   protected $casts = [
    'data' => 'array',
    'read_at' => 'datetime',
];



    
    public function sender()
{
    return $this->belongsTo(User::class, 'sender_id');
}

    
 public function getSenderUserAttribute()
{
    return User::find($this->data['sender_id'] ?? null);
}


}
