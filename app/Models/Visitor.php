<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Visitor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'hash',
        'ip_address',
        'user_agent',
        'browser',
        'os',
        'device',
        'country',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'referrer',
        'referrer_domain',
        'is_bot',
        'first_seen_at',
        'last_seen_at',
        // Modern Specs Columns
        'screen_resolution',
        'ram_gb',
        'cpu_cores',
        'network_type'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_bot' => 'boolean',
        'ram_gb' => 'float',
        'cpu_cores' => 'integer',
    ];

    // --- ১. Scopes (চার্টে ডেটা ফিল্টার করার জন্য সহজ উপায়) ---

    /**
     * শুধুমাত্র আসল ইউজারদের ফিল্টার করা (বট বাদ দিয়ে)
     */
    public function scopeRealUsers(Builder $query): void
    {
        $query->where('is_bot', false);
    }

    /**
     * যারা বর্তমানে অনলাইনে আছে (গত ৫ মিনিটে একটিভ)
     */
    public function scopeOnline(Builder $query): void
    {
        $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * নির্দিষ্ট নেটওয়ার্ক টাইপ অনুযায়ী ফিল্টার
     */
    public function scopeByNetwork(Builder $query, string $type): void
    {
        $query->where('network_type', $type);
    }

    // --- ২. Accessors (ব্লেড বা চার্টে সুন্দরভাবে নাম দেখানোর জন্য) ---

    /**
     * ইউজারের লোকেশন সুন্দরভাবে দেখানোর জন্য: "Dhaka, BD"
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn() => ($this->city && $this->country)
            ? "{$this->city}, {$this->country}"
            : 'Unknown'
        );
    }

    /**
     * RAM সুন্দরভাবে দেখানোর জন্য: "8 GB"
     */
    protected function ramFriendly(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ram_gb ? $this->ram_gb . ' GB' : 'N/A'
        );
    }

    // --- ৩. Relationships ---

    /**
     * সেশন ট্র্যাকিং
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    /**
     * পেজ ভিউ ট্র্যাকিং
     */
    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    /**
     * কাস্টম ইভেন্ট ট্র্যাকিং (ক্লিক, ডাউনলোড ইত্যাদি)
     */
    public function events(): HasMany
    {
        return $this->hasMany(VisitorEvent::class);
    }
}