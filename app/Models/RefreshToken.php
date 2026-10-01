<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $fillable = ['user_id', 'token', 'device_fingerprint', 'expires_at', 'last_used_at'];

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * নতুন refresh token issue করো।
     * Plain-text একবারই return হয় — DB-তে সবসময় hashed।
     */
    public static function issue(User $user, string $fingerprint = ''): string
    {
        $plain  = Str::random(64);
        $hashed = hash('sha256', $plain);

        // Same device-এর পুরনো token সরাও
        static::where('user_id', $user->id)
            ->where('device_fingerprint', $fingerprint)
            ->delete();

        static::create([
            'user_id'            => $user->id,
            'token'              => $hashed,
            'device_fingerprint' => $fingerprint,
            'expires_at'         => now()->addDays(30),
        ]);

        return $plain;
    }

    /**
     * Plain-text দিয়ে valid token খোঁজো।
     */
    public static function findValid(string $plain): ?static
    {
        return static::with('user')
            ->where('token', hash('sha256', $plain))
            ->where('expires_at', '>', now())
            ->first();
    }
}