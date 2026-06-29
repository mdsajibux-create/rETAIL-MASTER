<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\Translation\EmailTemplateTranslationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminEmailDetailsResource extends JsonResource
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
            "type" => $this->type,
            "name" => $this->name,
            "subject" => $this->subject,
            "body" => $this->body,
            "status" => $this->id,
            "translations" =>EmailTemplateTranslationResource::collection($this->related_translations->groupBy('language'))
        ];
    }
}
