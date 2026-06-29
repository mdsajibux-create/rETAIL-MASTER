<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\CurrencyTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "label" => $this->name .' '. '('.$this->symbol.')',
            "value" => $this->code,
            "symbol" => $this->symbol,
            "exchange_rate" => $this->exchange_rate,
            "is_default" => $this->is_default,
            "translations" => CurrencyTranslationResource::collection($this->translations->groupBy('language'))
        ];
    }
}
