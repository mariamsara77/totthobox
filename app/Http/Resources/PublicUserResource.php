<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'profession'     => $this->profession,
            'bio'            => $this->bio,
            'location'       => $this->location,
            'avatar_url'     => $this->getFirstMediaUrl('avatars', 'thumb') ?: $this->avatar_url,
            'roles'          => $this->getRoleNames(),
            'class_level'    => $this->whenLoaded('classLevel', fn () => $this->classLevel?->name),
            'division'       => $this->whenLoaded('division', fn () => $this->division?->name),
            'district'       => $this->whenLoaded('district', fn () => $this->district?->name),
            'thana'          => $this->whenLoaded('thana', fn () => $this->thana?->name),
            'member_since'   => $this->created_at?->format('d M Y'),
            'member_id'      => str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'is_verified'    => !is_null($this->email_verified_at),
        ];
    }
}