<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSelectListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');
        $translation = $this->related_translations->where('language', $language);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'behaviour' => $this->behaviour,
            'name' => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            'slug' => $this->slug,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'variants' => ProductVariantSelectResource::collection($this->variants),
            'status' => $this->status,
        ];
    }
}
