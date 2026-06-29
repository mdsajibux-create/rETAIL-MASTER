<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
        $translation = $this->related_translations->where('language', $language);
        return [
            'id' => $this->id,
            'title' => !empty($translation) && $translation->where('key', 'title')->first()
                ? $translation->where('key', 'title')->first()->value
                : $this->title, // If language is empty or not provided attribute
            'description' => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description, // If language is empty or not provided attribute
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'status' => $this->status,
            'created_by' => $this->creator?->first_name, // Safely handle null creator
        ];
    }
}
