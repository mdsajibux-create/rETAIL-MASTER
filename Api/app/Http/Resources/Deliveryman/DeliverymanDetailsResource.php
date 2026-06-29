<?php

namespace App\Http\Resources\Deliveryman;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Propaganistas\LaravelPhone\PhoneNumber;

class DeliverymanDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $phoneData = $this->getPhoneData($this->phone);
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'name' => $this->full_name,
            'phone' => $this->phone,
            'country_code' => $phoneData['country_code'],
            'e164_phone' => $phoneData['e164'],
            'iso_country_code' => $phoneData['iso_country'],
            'email' => $this->email,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'vehicle_type_id' => $this->deliveryman?->vehicle_type_id,
            'area_id' => $this->deliveryman?->area_id,
            'identification_type' => $this->deliveryman?->identification_type,
            'identification_number' => $this->deliveryman?->identification_number,
            'identification_photo_front' => $this->deliveryman?->identification_photo_front,
            'identification_photo_front_url' => asset('storage/' . $this->deliveryman?->identification_photo_front),
            'identification_photo_back' => $this->deliveryman?->identification_photo_back,
            'identification_photo_back_url' => asset('storage/' . $this->deliveryman?->identification_photo_back),
            'status' => $this->status,
            'is_verified' => (int)$this->is_verified,
            'verified_at' => $this->verified_at,
            'is_available' => (bool)$this->is_available,
            'email_verified' => $this->email_verified,
            "account_status" => $this->deactivated_at ? 'deactivated' : 'active',
            "marketing_email" => (bool)$this->marketing_email,
            "started_at" => $this->created_at->format('F d, Y'),
        ];
    }

    protected function getPhoneData($phone)
    {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();

            // If number starts with +, parse without region (auto-detect)
            $defaultRegion = null;

            // Optional: fallback region only if not in E.164 format
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }

            $numberProto = $phoneUtil->parse($phone, $defaultRegion);

            return [
                'e164' => $phoneUtil->format($numberProto, PhoneNumberFormat::E164),
                'country_code' => $numberProto->getCountryCode(), // e.g., 880
                'iso_country' => $phoneUtil->getRegionCodeForNumber($numberProto), // e.g., 'BD'
            ];
        } catch (NumberParseException $e) {
            return [
                'e164' => null,
                'country_code' => null,
                'iso_country' => null,
            ];
        }
    }
}
