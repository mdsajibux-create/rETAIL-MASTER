<?php

namespace Modules\Chat\app\Transformers;

use App\Actions\ImageModifier;
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
            'id'         => $this->id,
            'chat_id'    => $this->user_id,
            'user_type'    => $this->user_type,
            'created_at' => $this->created_at->toDateTimeString(),
            'user' => $this->user,
        ];
    }
}
