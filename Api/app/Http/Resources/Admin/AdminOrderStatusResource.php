<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            [
                'label' => 'All',
                'value' => '',
                'count' => $this->count(),
            ],
            [
                'label' => 'Pending',
                'value' => 'pending',
                'count' => $this->where('status', 'pending')->count(),
            ],
            [
                'label' => 'Confirmed',
                'value' => 'confirmed',
                'count' => $this->where('status', 'confirmed')->count(),
            ],
            [
                'label' => 'Processing',
                'value' => 'processing',
                'count' => $this->where('status', 'processing')->count(),
            ],
            [
                'label' => 'Pickup',
                'value' => 'pickup',
                'count' => $this->where('status', 'pickup')->count(),
            ],
            [
                'label' => 'Shipped',
                'value' => 'shipped',
                'count' => $this->where('status', 'shipped')->count(),
            ],
            [
                'label' => 'Delivered',
                'value' => 'delivered',
                'count' => $this->where('status', 'delivered')->count(),
            ],
            [
                'label' => 'Cancelled',
                'value' => 'cancelled',
                'count' => $this->where('status', 'cancelled')->count(),
            ],
            [
                'label' => 'Refunded',
                'value' => 'refunded',
                'count' => $this->whereIn('refund_status', ['requested', 'refunded','approved'])->count(),
            ],
        ];
    }
}
