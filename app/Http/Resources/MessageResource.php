<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attachment = $this->getFirstMedia('attachments');

        return [
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message' => $this->message,
            'parent_id' => $this->parent_id,
            'read' => (bool) $this->read,
            'read_at' => $this->read_at,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'attachment' => $attachment ? [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'original_name' => $attachment->name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'url' => $attachment->getUrl(),
            ] : null,
            'sender' => $this->whenLoaded('sender'),
            'receiver' => $this->whenLoaded('receiver'),
            'parent' => $this->whenLoaded('parent'),
        ];
    }
}