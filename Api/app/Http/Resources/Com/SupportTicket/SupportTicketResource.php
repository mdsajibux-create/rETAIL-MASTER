<?php

namespace App\Http\Resources\Com\SupportTicket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
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
            'title' => $this->title,
            'priority' => $this->priority,
            'status' => (int)$this->status,
            'department' => $this->department?->name,
            'created_by' => $this->customer ? $this->customer->getFullNameAttribute() . ' | Customer' : null,
        ];
    }
}
