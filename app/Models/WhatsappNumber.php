<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNumber extends Model
{
    protected $table = 'whatsapp_numbers';

    protected $fillable = [
        'phone_number',
        'status',
        'campaign_id',
        'sent_at',
        'failed_at',
        'error_message',
        'retry_count',
        'last_retry_at',
        'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'last_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSkipped($query)
    {
        return $query->where('status', 'skipped');
    }

    public function scopeInvalid($query)
    {
        return $query->where('status', 'invalid');
    }

    public function scopeByCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
            'last_retry_at' => now(),
        ]);
    }

    public function markAsSkipped()
    {
        $this->update([
            'status' => 'skipped',
            'failed_at' => now(),
        ]);
    }

    public function markAsInvalid()
    {
        $this->update([
            'status' => 'invalid',
            'failed_at' => now(),
        ]);
    }

    public static function sanitizePhoneNumber($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 10) {
            $phone = '88' . $phone;
        }
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        return $phone;
    }

    public static function isValidPhoneNumber($phone)
    {
        $sanitized = self::sanitizePhoneNumber($phone);
        return preg_match('/^\+880\d{9}$/', $sanitized);
    }
}