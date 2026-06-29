<?php

namespace App\Http\Resources\Customer;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDetailsResource extends JsonResource
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
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "full_name" => $this->full_name,
            "email" => $this->email,
            "phone" => $this->phone,
            "image" => ImageModifier::generateImageUrl($this->image),
            "birth_day" => $this->birth_day,
            "gender" => $this->gender,
            "def_lang" => $this->def_lang,
            "password_changed_at" => $this->password_changed_at,
            "email_verify_token" => $this->email_verify_token,
            "email_verified" => $this->email_verified,
            "email_verified_at" => $this->email_verified_at,
            "verified" => $this->verified,
            "verify_method" => $this->verify_method,
            "marketing_email" => $this->marketing_email,
            "marketing_sms" => $this->marketing_sms,
            "status" => $this->status,
            "deleted_at" => $this->deleted_at,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
