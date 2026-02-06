<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_id',
        'session_id',
        'url',
        'route_name',
        'method',
        'status_code',
        'query_params',
        'headers',
        'load_time',
        'is_ajax',
        'is_pjax',
        'is_secure',
    ];

    protected $casts = [
        'query_params' => 'array',
        'headers' => 'array',
        'is_ajax' => 'boolean',
        'is_pjax' => 'boolean',
        'is_secure' => 'boolean',
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