<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\CurrencyTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminCurrencyDetailsResource extends JsonResource
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
            "name" => $this->name,
            "code" => $this->code,
            "symbol" => $this->symbol,
            "exchange_rate" => $this->exchange_rate,
            "is_default" => $this->is_default,
            "status" => $this->status,
            "translations" => CurrencyTranslationResource::collection($this->translations->groupBy('language'))
        ];
    }
}
