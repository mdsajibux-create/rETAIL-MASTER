<?php

namespace Modules\Product\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleWithProductPublicResource extends JsonResource
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
            "title" => !empty($translation) && $translation->where('key', 'title')->first()
                ? $translation->where('key', 'title')->first()->value
                : $this->title,
            "title_color" => $this->title_color,
            "description" => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description,
            "description_color" => $this->description_color,
            "background_color" => $this->background_color,
            "image" => $this->image,
            "image_url" => ImageModifier::generateImageUrl($this->image),
            "cover_image" => $this->cover_image,
            "cover_image_url" => ImageModifier::generateImageUrl($this->cover_image),
            "discount_type" => $this->discount_type,
            "discount_amount" => $this->discount_amount,
            "special_price" => $this->special_price,
            "button_text" => !empty($translation) && $translation->where('key', 'button_text')->first()
                ? $translation->where('key', 'button_text')->first()->value
                : $this->button_text,
            "button_text_color" => $this->button_text_color,
            "button_hover_color" => $this->button_hover_color,
            "button_bg_color" => $this->button_bg_color,
            "button_url" => $this->button_url,
            "timer_bg_color" => $this->timer_bg_color,
            "timer_text_color" => $this->timer_text_color,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
        ];
    }
}
