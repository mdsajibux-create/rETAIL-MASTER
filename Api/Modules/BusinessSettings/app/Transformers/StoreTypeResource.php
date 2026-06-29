<?php

namespace Modules\BusinessSettings\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreTypeResource extends JsonResource
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
            "id" => $this->id,
            "name" => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name,
            "type" => $this->type,
            "image" => ImageModifier::generateImageUrl($this->image),
            "description" => !empty($translation) && $translation->where('key', 'description')->first()
                ? $translation->where('key', 'description')->first()->value
                : $this->description,
            "charge_status" => $this->charge_status,
            "charge_name" => !empty($translation) && $translation->where('key', 'charge_name')->first()
                ? $translation->where('key', 'charge_name')->first()->value
                : $this->charge_name,
            "charge_amount" => $this->charge_amount,
            "charge_type" => $this->charge_type,
            "status" => (int)$this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
