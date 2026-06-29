<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\SliderTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSliderResource extends JsonResource
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
            "id" => $this->id,
            "platform" => $this->platform,
            "theme_name" => $this->theme_name,
            "title" => !empty($translation) && $translation->where('key', 'title')->first()
                ? $translation->where('key', 'title')->first()->value
                : $this->title ?? null, // If language is empty or not provided attribute
            "title_color" => $this->title_color,
            "sub_title" => !empty($translation) && $translation->where('key', 'sub_title')->first()
                ? $translation->where('key', 'sub_title')->first()->value
                : $this->sub_title ?? null, // If language is empty or not provided attribute
            "sub_title_color" => $this->sub_title_color,
            "description" => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description ?? null, // If language is empty or not provided attribute
            "description_color" => $this->description_color,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "bg_image" => $this->bg_image,
            "bg_image_url" => ImageModifier::generateImageUrl($this->bg_image),
            "button_text" => !empty($translation) && $translation->where('key', 'button_text')->first()
                ? $translation->where('key', 'button_text')->first()->value
                : $this->button_text ?? null, // If language is empty or not provided attribute
            "button_text_color" => $this->button_text_color,
            "button_bg_color" => $this->button_bg_color,
            "bg_color" => $this->bg_color,
            "button_hover_color" => $this->button_hover_color,
            "button_url" => $this->button_url,
            "redirect_url" => $this->redirect_url,
            "order" => $this->order,
            "status" => $this->status,
            "created_by" => $this->created_by,
            "updated_by" => $this->updated_by,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "translations" => SliderTranslationResource::collection($this->related_translations->groupBy('language')),
        ];
    }
}
