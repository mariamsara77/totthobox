<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Minister extends BaseModel implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'ministers';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'name',
        'image',
        'designation',
        'rank',
        'party',
        'from_date',
        'to_date',
        'is_current',
        'bio',
        'division_id',
        'district_id',
        'thana_id',
        'slug',
        'user_id',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'updated_by',
        'deleted_by',
        'published_at',
        'published_by',
        'view_count',
        'is_featured',
        'ip_address',
        'user_agent',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_current' => 'boolean',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];


    /**
     * Relationships
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }
}