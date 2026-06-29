<?php

namespace Modules\Subscription\app\Transformers;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\SubspriptionPackageTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSubscriptionPackageDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, // If language is empty or not provided attribute
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
            'description' => $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'payment_gateway' => $this->payment_gateway,
            'payment_status' => $this->payment_status,
            'expire_date' => $this->expire_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'translations' => SubspriptionPackageTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
