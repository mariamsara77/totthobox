<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'email'         => $this->email,
            'phone'         => $this->phone,
            'avatar'        => $this->getFirstMediaUrl('avatars', 'thumb') ?: null,
            'is_online'     => $this->isOnline(),
            'last_active_at'=> $this->last_active_at?->toISOString(),
            'last_seen'     => $this->last_active_at
                                ? $this->last_active_at->diffForHumans()
                                : 'long ago',
            'roles'         => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
        ];
    }
}