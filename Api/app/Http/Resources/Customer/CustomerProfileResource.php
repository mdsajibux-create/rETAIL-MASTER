<?php

namespace App\Http\Resources\Customer;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class CustomerProfileResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        $phoneData = $this->getPhoneData($this->phone);
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->fullname,
            'phone' => $this->phone,
            'country_code' => $phoneData['country_code'],
            'e164_phone' => $phoneData['e164'],
            'iso_country_code' => $phoneData['iso_country'],
            'email' => $this->email,
            'birth_day' => $this->birth_day,
            'gender' => $this->gender,
            'image' => $this->image,
            'image_url' => ImageModifier::generateImageUrl($this->image),
            'status' => $this->status,
            'email_verified' => (bool)$this->email_verified,
            'unread_notifications' => $this->unread_notifications,
            'wishlist_count' => $this->wishlist_count,
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
