<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WhatsappTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name',
        'message_body',
        'image_url',
        'image_name',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(WhatsappCampaign::class, 'campaign_templates');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function incrementUsageCount()
    {
        $this->increment('usage_count');
    }

    public function getProcessedMessage($variables = [])
    {
        $message = $this->message_body;

        $defaultVariables = [
            '{time}' => now()->format('H:i:s'),
            '{date}' => now()->format('Y-m-d'),
            '{name}' => 'Friend',
        ];

        $allVariables = array_merge($defaultVariables, $variables);

        foreach ($allVariables as $key => $value) {
            $message = str_replace($key, $value, $message);
        }

        return $message;
    }
}