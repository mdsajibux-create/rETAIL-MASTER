<?php

namespace App\Http\Resources\Com\SupportTicket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SupportTicketMessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_details' => new SupportTicketDetailsResource($this->ticket),
            'sender_details' => new SenderDetailsResource($this->sender),
            'receiver_details' => new ReceiverDetailsResource($this->receiver),
            'message' => [
                'from' => $this->sender_role === 'customer_level'
                    ? $this->sender?->getFullNameAttribute()
                    : ($this->sender_role === 'store_level'
                        ? $this->sender?->name
                        : ($this->sender_role === 'system_level'
                            ? $this->receiver?->getFullNameAttribute()
                            : 'Not received yet!'
                        )
                    ),
                'role' => $this->sender_role ?? $this->receiver_role,
                'message' => $this->message,
                'file' => $this->file ? asset('storage/' . $this->file) : null,
                'timestamp' => $this->created_at->diffForHumans()
            ]
        ];
    }
}
