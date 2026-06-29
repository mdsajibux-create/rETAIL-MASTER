<?php

namespace App\Http\Resources\Admin;

use App\Actions\ImageModifier;
use App\Http\Resources\Translation\SliderTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminSliderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "platform" => $this->platform,
            "theme_name" =>$this->theme_name,
            "title" =>$this->title,
            "title_color" => $this->title_color,
            "sub_title" => $this->sub_title,
            "sub_title_color" => $this->sub_title_color,
            "description" => $this->description,
            "description_color" => $this->description_color,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "bg_image" => $this->bg_image,
            "bg_image_url" => ImageModifier::generateImageUrl($this->bg_image),
            "button_text" =>$this->button_text,
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
