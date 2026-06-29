<?php

namespace Modules\Subscription\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreSubscriptionHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'store' => $this->store?->name,
            'store_slug' => $this->store?->slug,
            'image' => $this->subscription?->image,
            'image_url' => ImageModifier::generateImageUrl($this->subscription?->image),
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
            'payment_gateway' => $this->payment_gateway,
            'payment_status' => $this->payment_status,
            'transaction_ref' => $this->transaction_ref,
            'manual_image' => $this->manual_image,
            'expire_date' => $this->expire_date,
            'status' => $this->status,
        ];
    }
}
