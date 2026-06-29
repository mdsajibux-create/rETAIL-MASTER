<?php

namespace Modules\Chat\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'chat_id'         => $this->id,
            'user_id'    => $this->user_id,
            'user_type'    => $this->user_type,
            'sender_id'    => $this->getSenderId(),
            'user' => new UserInfoForChatResource($this->whenLoaded('user'), $this->user_type),
        ];
    }

    protected function getSenderId(): ?int
    {
        if (auth('api')->check()) {
            return auth('api')->id();
        }

        if (auth('api_customer')->check()) {
            return auth('api_customer')->id();
        }

        return null;
    }
}
