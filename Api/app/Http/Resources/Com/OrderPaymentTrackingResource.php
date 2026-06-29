<?php

namespace App\Http\Resources\Com;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPaymentTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'label' => match ($this->activity_value) {
                'pending'         => 'Waiting for payment to be completed.',
                'partially_paid'  => 'A partial payment has been received.',
                'paid'            => 'The full payment has been successfully received.',
                'cancelled'       => 'The payment was cancelled by the user or system.',
                'failed'          => 'The payment could not be processed.',
                'refunded'        => 'The payment has been returned to the customer.',
                default      => ucfirst($this->activity_value),
            },
            'status' => $this->activity_value,
            'created_at' => Carbon::parse($this->created_at)->format('d M Y, h:i A'),
        ];
    }
}
