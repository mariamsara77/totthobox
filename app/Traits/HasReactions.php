<?php

namespace App\Traits;

use App\Models\Reaction;

trait HasReactions
{
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function react($type)
    {
        // Remove previous reaction by same user
        $this->reactions()
             ->where('user_id', auth()->id())
             ->delete();

        // Add new reaction
        return $this->reactions()->create([
            'user_id' => auth()->id(),
            'type' => $type,
        ]);
    }

    public function hasReaction($type)
    {
        return $this->reactions()
            ->where('user_id', auth()->id())
            ->where('type', $type)
            ->exists();
    }

    public function countReaction($type)
    {
        return $this->reactions()
            ->where('type', $type)
            ->count();
    }

    public function userReaction()
    {
        return $this->reactions()
            ->where('user_id', auth()->id())
            ->value('type');
    }
}
