<?php

namespace Modules\PaymentGateways\App\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewaysResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'description' => $this->description,
            'auth_credentials' => !empty($this->auth_credentials)
                ? json_decode($this->auth_credentials, true)
                : null,
            'is_test_mode' => $this->is_test_mode,
            'status' => $this->status
        ];
    }
}
