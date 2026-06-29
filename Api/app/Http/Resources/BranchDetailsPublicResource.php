<?php

namespace App\Http\Resources;

use App\Actions\MultipleImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchDetailsPublicResource extends JsonResource
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

        $address = $this->address;
        if ($translation->isNotEmpty()) {
            $translatedAddress = $translation->where('key', 'address')->first()?->value;

            if (!empty($translatedAddress)) {
                $address = $translatedAddress;
            }
        }

        return [
            'id' => $this->id,
            'is_web' => $this->is_web,
            'is_main' => $this->is_main,
            'zone' => $this->zone->name ?? null,
            'zone_id' => $this->zone_id,
            'name' => $translation->isNotEmpty()
                ? $translation->where('key', 'name')->first()?->value
                : $this->name,
            'slug' => $this->slug,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $address,
            'is_featured' => $this->is_featured,
            'opening_time' => $this->opening_time,
            'closing_time' => $this->closing_time,
            'off_day' => $this->off_day,
            "gallery_images_urls" => MultipleImageModifier::multipleImageModifier($this->gallery_images),
        ];
    }
}
