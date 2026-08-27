<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YoutubeStream extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'youtube_streams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_url',
        'stream_link',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}