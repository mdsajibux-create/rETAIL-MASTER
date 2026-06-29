<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminQueriesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the requested language from the query parameter
        $language = $request->input('language', 'en');
        // Get the translation for the requested language
        $product_translation = $this->product?->related_translations->where('language', $language);
        $store_translation = $this->store?->related_translations->where('language', $language);
        return [
            "id" => $this->id,
            "product_id" => $this->product_id,
            "customer_id" => $this->customer_id,
            "question" => $this->question,
            "store" => !empty($store_translation) && $store_translation->where('key', 'name')->first()
                ? $store_translation->where('key', 'name')->first()->value
                : $this->store?->name,
            "store_slug" => $this->store?->slug,
            "reply" => $this->reply,
            "replied_at" => $this->replied_at,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "product" => !empty($product_translation) && $product_translation->where('key', 'name')->first()
                ? $product_translation->where('key', 'name')->first()->value
                : $this->product?->name,
            "product_image" => $this->product?->image,
            "product_image_url" => ImageModifier::generateImageUrl($this->product?->image) ?? null,
            "slug" => $this->product?->slug,
            "customer" => $this->customer->getFullNameAttribute() ?? null,
        ];
    }
}
