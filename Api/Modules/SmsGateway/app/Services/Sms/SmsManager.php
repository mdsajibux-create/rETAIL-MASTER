<?php

namespace Modules\SmsGateway\app\Services\Sms;

use Modules\SmsGateway\app\Services\Sms\Providers\NexmoSmsService;
use Modules\SmsGateway\app\Services\Sms\Providers\TwilioSmsService;
use Modules\SmsGateway\App\Services\Sms\SmsInterface;
use Modules\SmsGateway\app\Models\SmsProvider;

class SmsManager
{
    public static function driver(): SmsInterface
    {
        $provider = SmsProvider::where('status', 1)->first();

        if (!$provider) {
            throw new \Exception('No active SMS provider found.');
        }

        $credentials = json_decode($provider->credentials, true);

        return match ($provider->slug) {
            'nexmo' => new NexmoSmsService($credentials),
            'twilio' => new TwilioSmsService($credentials),
            default => throw new \Exception('Unsupported SMS provider: ' . $provider->slug),
        };
    }

    public static function getExpireAt(): \Illuminate\Support\Carbon
    {
        $provider = SmsProvider::where('status', 1)->first();

        if (!$provider || !$provider->expire_time) {
            throw new \Exception('Active SMS provider or expire time not found.');
        }

        return now()->addMinutes($provider->expire_time);
    }

    public static function send(string $to, string $message): bool
    {
        return self::driver()->send($to, $message);
    }

}