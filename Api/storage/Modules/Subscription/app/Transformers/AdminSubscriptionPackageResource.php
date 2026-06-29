<?php

namespace Modules\Subscription\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSubscriptionPackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $translation = $this->related_translations->where('language', $language);
        return [
            'id' => $this->id,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
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
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'payment_gateway' => $this->payment_gateway,
            'payment_status' => $this->payment_status,
            'expire_date' => $this->expire_date,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
