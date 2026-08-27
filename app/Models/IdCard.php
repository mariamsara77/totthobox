<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class IdCard extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    /**
     * All supported templates — single source of truth for UI, validation & rendering
     */
    public const TEMPLATES = [
        'aurora',     // Gradient purple-indigo, floating orbs
        'corporate',  // Clean white, top accent bar, professional
        'neon',       // Dark background, cyan glow, futuristic
        'royal',      // Dark brown-gold, luxury feel
        'glass',      // Frosted glass morphism
        'geometric',  // Geometric shapes, bold angles
        'minimal',    // Ultra-clean, typography-first
        'split',      // Left photo panel, right info panel
        'vertical',   // Portrait orientation, stacked layout
        'ocean',      // Deep blue gradient, wave motif
        'sunset',     // Warm orange-red gradient
        'carbon',     // Dark carbon fiber texture look
    ];

    public const CARD_TYPES = [
        'employee', 'student', 'membership', 'press', 'visitor', 'contractor', 'custom',
    ];

    public const STATUSES = ['active', 'expired', 'revoked', 'suspended'];

    protected $fillable = [
        'uuid', 'user_id', 'card_number', 'card_type', 'status',
        'template_name', 'language', 'design_settings',
        'name_en', 'designation_en', 'department_en', 'organization_en', 'address_en',
        'name_bn', 'designation_bn', 'department_bn', 'organization_bn', 'address_bn',
        'blood_group', 'date_of_birth', 'nid_or_passport',
        'phone', 'email', 'emergency_contact_name', 'emergency_contact_phone',
        'issue_date', 'expiry_date',
        'nfc_serial', 'barcode_data', 'custom_fields',
    ];

    protected $casts = [
        'design_settings' => 'array',
        'custom_fields'   => 'array',
        'date_of_birth'   => 'date',
        'issue_date'      => 'date',
        'expiry_date'     => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (IdCard $card) {
            $card->uuid ??= (string) Str::uuid();

            if (blank($card->card_number)) {
                do {
                    $candidate = 'IDC-' . strtoupper(Str::random(8));
                } while (static::where('card_number', $candidate)->exists());
                $card->card_number = $candidate;
            }

            $card->barcode_data ??= $card->card_number;
        });
    }

    /* ======================================================
     * Relationships
     * ====================================================== */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ======================================================
     * Media Collections
     * ====================================================== */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('photo')
            ->width(150)->height(150)->sharpen(10)->nonQueued();

        $this->addMediaConversion('card')
            ->performOnCollections('photo')
            ->width(400)->height(500)->sharpen(8)->nonQueued();

        $this->addMediaConversion('print')
            ->performOnCollections('photo')
            ->width(800)->height(1000)->quality(95)->nonQueued();

        $this->addMediaConversion('logo_card')
            ->performOnCollections('logo')
            ->width(300)->height(120)->nonQueued();
    }

    /* ======================================================
     * URL Accessors (single definition — no duplicates)
     * ====================================================== */

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('photo', 'card') ?: null);
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('logo', 'logo_card') ?: null);
    }

    protected function photoPrintUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('photo', 'print') ?: null);
    }

    public function hasPhoto(): bool
    {
        return $this->getFirstMedia('photo') !== null;
    }

    public function hasLogo(): bool
    {
        return $this->getFirstMedia('logo') !== null;
    }

    /* ======================================================
     * Language / Display Helpers
     * ====================================================== */

    /**
     * Pick correct field value based on language setting.
     * Falls back gracefully if one language is empty.
     */
    public function resolve(string $enField, string $bnField): ?string
    {
        $en = $this->{$enField};
        $bn = $this->{$bnField};

        return match ($this->language) {
            'en'    => $en ?: $bn,
            'bn'    => $bn ?: $en,
            default => $bn ?: $en, // 'both' — primary Bengali
        };
    }

    /**
     * Returns the secondary (English) name when language = 'both' and both are set.
     */
    public function secondaryName(): ?string
    {
        if (($this->language ?? 'both') !== 'both') {
            return null;
        }

        return ($this->name_en && $this->name_bn) ? $this->name_en : null;
    }

    protected function displayName(): Attribute
    {
        return Attribute::get(fn () => $this->resolve('name_en', 'name_bn') ?: 'N/A');
    }

    protected function displayDesignation(): Attribute
    {
        return Attribute::get(fn () => $this->resolve('designation_en', 'designation_bn'));
    }

    protected function displayOrganization(): Attribute
    {
        return Attribute::get(fn () => $this->resolve('organization_en', 'organization_bn'));
    }

    protected function displayDepartment(): Attribute
    {
        return Attribute::get(fn () => $this->resolve('department_en', 'department_bn'));
    }

    /* ======================================================
     * Status / Expiry Helpers
     * ====================================================== */

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && ! $this->isExpired();
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->expiry_date, false));
    }

    /* ======================================================
     * URL / QR Helpers
     * ====================================================== */

    public function verifyUrl(): string
    {
        return url('/verify/' . $this->uuid);
    }

    public function qrImageUrl(int $size = 150): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&data=' . urlencode($this->verifyUrl())
            . '&color=000000&bgcolor=ffffff&margin=4';
    }

    /* ======================================================
     * Routing
     * ====================================================== */

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}