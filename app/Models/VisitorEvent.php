<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'session_id',
        'event_type',
        'event_name',
        'event_data',
    ];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class);
    }

    public function visitorSession()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id');
    }
}