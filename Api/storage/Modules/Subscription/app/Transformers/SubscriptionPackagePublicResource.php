<?php

namespace Modules\Subscription\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPackagePublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'validity' => $this->validity,
            'price' => $this->price,
            'pos_system' => $this->pos_system,
            'self_delivery' => $this->self_delivery,
            'mobile_app' => $this->mobile_app,
            'live_chat' => $this->live_chat,
            'order_limit' => $this->order_limit,
            'product_limit' => $this->product_limit,
            'product_featured_limit' => $this->product_featured_limit,
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
        ];
    }
}
