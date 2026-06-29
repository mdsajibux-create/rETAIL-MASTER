<?php

namespace Modules\Chat\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'chat_id'    => $this->chat_id,
            'sender_id'  => $this->sender_id,
            'sender_type'  => $this->sender_type,
            'receiver_id'  => $this->receiver_id,
            'receiver_type'  => $this->receiver_type,
            'message'    => $this->message,
            'file'       => $this->file ? asset('storage/' . $this->file) : null,
            'is_seen'    => $this->is_seen,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
