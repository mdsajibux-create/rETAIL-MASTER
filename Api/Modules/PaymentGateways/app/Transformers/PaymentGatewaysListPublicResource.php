<?php

namespace Modules\PaymentGateways\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewaysListPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->slug,
            'label' => $this->name,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'description' => $this->description,
            'auth_credentials' => json_decode($this->auth_credentials, true),
        ];
    }
}
