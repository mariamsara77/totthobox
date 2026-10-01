<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'email'           => $this->email,
            'profession'      => $this->profession,
            'bio'             => $this->bio,
            'location'        => $this->location,
            'avatar_url'      => $this->getFirstMediaUrl('avatars', 'thumb') ?: $this->avatar_url,
            'division_id'     => $this->division_id,
            'district_id'     => $this->district_id,
            'thana_id'        => $this->thana_id,
            'class_level_id'  => $this->class_level_id,
            'selected_role'   => $this->getRoleNames()->first() ?? 'user',
            'roles'           => $this->getRoleNames(),
            'division'        => $this->whenLoaded('division', fn () => $this->division?->only(['id', 'name'])),
            'district'        => $this->whenLoaded('district', fn () => $this->district?->only(['id', 'name'])),
            'thana'           => $this->whenLoaded('thana', fn () => $this->thana?->only(['id', 'name'])),
            'class_level'     => $this->whenLoaded('classLevel', fn () => $this->classLevel?->only(['id', 'name'])),
            'email_verified'  => !is_null($this->email_verified_at),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}