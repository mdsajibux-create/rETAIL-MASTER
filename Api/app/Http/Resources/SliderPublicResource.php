<?php

namespace App\Http\Resources;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $language = $request->input('language', 'en');

        // Key the collection once by 'key' field — O(1) lookups instead of repeated scans
        $translations = $this->related_translations
            ->where('language', $language)
            ->keyBy('key');

        // Helper to fall back cleanly
        $t = fn(string $key, mixed $fallback) =>
            $translations->get($key)?->value ?? $fallback;

        return [
            'id'                  => $this->id,
            'theme_name'          => $this->theme_name,
            'title'               => $t('title', $this->title),
            'title_color'         => $this->title_color,
            'sub_title'           => $t('sub_title', $this->sub_title),
            'sub_title_color'     => $this->sub_title_color,
            'description'         => $t('description', $this->description),
            'description_color'   => $this->description_color,
            'image'               => $this->image,
            'image_url'           => ImageModifier::generateImageUrl($this->image),
            'bg_image'            => $this->bg_image,
            'bg_image_url'        => ImageModifier::generateImageUrl($this->bg_image),
            'bg_color'            => $this->bg_color,
            'button_text'         => $t('button_text', $this->button_text),
            'button_text_color'   => $this->button_text_color,
            'button_bg_color'     => $this->button_bg_color,
            'button_hover_color'  => $this->button_hover_color,
            'button_url'          => $this->button_url,
            'redirect_url'        => $this->redirect_url,
        ];
    }
}
