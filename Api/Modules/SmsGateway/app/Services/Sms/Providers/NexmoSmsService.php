<?php

namespace Modules\SmsGateway\app\Services\Sms\Providers;

use Modules\SmsGateway\app\Services\Sms\SmsInterface;
use Vonage\Client;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class NexmoSmsService implements SmsInterface
{
    protected Client $client;
    protected string $from;

    public function __construct(array $credentials)
    {
        $basic = new Basic($credentials['nexmo_api_key'], $credentials['nexmo_api_secret']);
        $this->client = new Client($basic);
        $this->from = $credentials['from'] ?? 'Bravo Mart';
    }

    public function send(string $to, string $message): bool
    {
        try {
            $response = $this->client->sms()->send(
                new SMS($to, $this->from, $message)
            );

            return $response->current()->getStatus() === 0;
        } catch (\Throwable $e) {
            logger()->error("Vonage SMS Error: " . $e->getMessage());
            return false;
        }
    }
}