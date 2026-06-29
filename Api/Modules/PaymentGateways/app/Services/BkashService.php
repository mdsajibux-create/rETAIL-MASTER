<?php

namespace Modules\PaymentGateways\app\Services;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;


class BkashService
{
    public function grantToken($credentials): string
    {
        $cacheKey = 'bkash_id_token_' . ($credentials['bkash_app_key'] ?? 'default');

        return Cache::remember($cacheKey, now()->addMinutes(55), function () use ($credentials) {

            $baseUrl = rtrim($credentials['bkash_base_url'], '/');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'username'     => $credentials['bkash_username'],
                'password'     => $credentials['bkash_password'],
            ])->post(
                $baseUrl . '/tokenized/checkout/token/grant',
                [
                    'app_key'    => $credentials['bkash_app_key'] ?? null,
                    'app_secret' => $credentials['bkash_app_secret'] ?? null,
                ]
            )->throw()->json();


            return $response['id_token'];
        });
    }

    public function createPayment(array $payload, $credentials = null): array
    {
        $token = $this->grantToken($credentials);

        $baseUrl = rtrim($credentials['bkash_base_url'], '/');

        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Authorization' => $token,
            'username'      => $credentials['bkash_username'],
            'password'      => $credentials['bkash_password'],
            'X-App-Key'    => $credentials['bkash_app_key'] ?? null,
        ])->post($baseUrl . '/tokenized/checkout/create', $payload)
            ->throw()
            ->json();
    }

    public function executePayment(string $paymentId, $credentials = null): array
    {
        $token = $this->grantToken($credentials);

        $baseUrl = rtrim($credentials['bkash_base_url'], '/');

        return Http::withHeaders([
            'Accept'       => 'application/json',
            'Authorization' => $token,
            'username'      => $credentials['bkash_username'],
            'password'      => $credentials['bkash_password'],
            'X-App-Key'    => $credentials['bkash_app_key'] ?? null,
        ])->post($baseUrl . '/tokenized/checkout/execute', ['paymentID' => $paymentId])->throw()->json();
    }

    public function queryPayment(string $paymentId, $credentials = null): array
    {
        $token = $this->grantToken($credentials);
        $baseUrl = rtrim($credentials['bkash_base_url'], '/');

        return Http::withHeaders([
            'Accept'       => 'application/json',
            'Authorization' => $token,
            'username'      => $credentials['bkash_username'],
            'password'      => $credentials['bkash_password'],
            'X-App-Key'     => $credentials['bkash_app_key'],
        ])->post($baseUrl . '/tokenized/checkout/payment/status', [
            'paymentID' => $paymentId,
        ])->throw()->json();
    }


}
