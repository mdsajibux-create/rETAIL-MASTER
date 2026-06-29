<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\SettingsTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminContactSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'status' => $this->status,
            "slug" => $this->slug,
            "content" => $this->content,
            "translations"=>SettingsTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
