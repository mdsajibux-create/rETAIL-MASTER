<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class CustomerAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $phoneData = $this->getPhoneData($this->contact_number);

        return [
            "id" => $this->id,
            "title" => $this->title,
            "type" => $this->type,
            "email" => $this->email,
            "contact_number" => $this->contact_number,
            'country_code' => $phoneData['country_code'],
            'e164_phone' => $phoneData['e164'],
            'iso_country_code' => $phoneData['iso_country'],
            "address" => $this->address,
            "lat" => $this->latitude,
            "long" => $this->longitude,
            "zone" => $this->zone,
            "state" => $this->state,
            "city" => $this->city,
            "area" => $this->area,
            "road" => $this->road,
            "house" => $this->house,
            "floor" => $this->floor,
            "postal_code" => $this->postal_code,
            "is_default" => (bool)$this->is_default,
            "status" => $this->status,
        ];
    }

    public function with($request): array
    {
        return [
            'message' => __('messages.data_found'),
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
