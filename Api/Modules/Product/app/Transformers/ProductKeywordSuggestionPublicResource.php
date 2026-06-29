<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductKeywordSuggestionPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'keyword' => $this->name,
            'slug'    => $this->slug,
        ];
    }
}
