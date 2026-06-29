<?php

namespace App\Http\Resources\Com;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderRefundTrackingResource extends JsonResource
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
                'requested'  => 'The customer has submitted a refund request.',
                'processing' => 'The refund request is being reviewed by the team.',
                'refunded'   => 'The refund has been completed successfully.',
                'rejected'   => 'The refund request was rejected due to policy or other reasons.',
                default      => ucfirst($this->activity_value),
            },
            'status' => $this->activity_value,
            'created_at' => Carbon::parse($this->created_at)->format('d M Y, h:i A'),
        ];
    }
}
