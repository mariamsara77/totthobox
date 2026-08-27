<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WhatsappCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'name',
        'description',
        'status',
        'total_numbers',
        'sent_count',
        'failed_count',
        'skipped_count',
        'pending_count',
        'min_delay',
        'max_delay',
        'daily_limit',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function numbers(): HasMany
    {
        return $this->hasMany(WhatsappNumber::class, 'campaign_id');
    }

    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(WhatsappTemplate::class, 'campaign_templates');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }

    public function updateStats()
    {
        $this->update([
            'sent_count' => $this->numbers()->sent()->count(),
            'failed_count' => $this->numbers()->failed()->count(),
            'skipped_count' => $this->numbers()->skipped()->count(),
            'pending_count' => $this->numbers()->pending()->count(),
        ]);
    }

    public function getProgress()
    {
        $total = $this->total_numbers;
        if ($total == 0)
            return 0;

        $completed = $this->sent_count + $this->failed_count + $this->skipped_count;
        return round(($completed / $total) * 100, 2);
    }

    public function start()
    {
        $this->update([
            'status' => 'active',
            'started_at' => now(),
        ]);
    }

    public function pause()
    {
        $this->update([
            'status' => 'paused',
        ]);
    }

    public function resume()
    {
        $this->update([
            'status' => 'active',
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function getNextPendingNumber()
    {
        return $this->numbers()
            ->where('status', 'pending')
            ->lockForUpdate()
            ->first();
    }

    public function getRandomTemplate()
    {
        return $this->templates()
            ->active()
            ->inRandomOrder()
            ->first();
    }
}