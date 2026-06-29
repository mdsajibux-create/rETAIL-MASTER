<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminEmailResource extends JsonResource
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
            "type" => $this->type,
            "name" => !empty($translation) && $translation->where('key', 'name')->first()
                ? $translation->where('key', 'name')->first()->value
                : $this->name, // If language is empty or not provided attribute
            "subject" => !empty($translation) && $translation->where('key', 'subject')->first()
                ? $translation->where('key', 'subject')->first()->value
                : $this->subject, // If language is empty or not provided attribute
            "body" => !empty($translation) && $translation->where('key', 'body')->first()
                ? $translation->where('key', 'body')->first()->value
                : $this->body, // If language is empty or not provided attribute
            "status" => (int)$this->status,
        ];
    }
}
