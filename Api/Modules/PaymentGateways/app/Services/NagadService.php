<?php

namespace Modules\PaymentGateways\app\Services;


use Illuminate\Support\Facades\Http;
use RuntimeException;


class NagadService
{


    public function verifyPayment(string $paymentRefId, array $credentials): array
    {
        return 0;
    }


    public function createPayment(array $payload, $credentials = null): array
    {
        if ($credentials['is_test_mode']) { // if live mode
            $baseUrl =  'https://api-sandbox.mynagad.com/api/dfs';
            $url = rtrim($baseUrl, '/') . "/check-out/initialize/{$credentials['nagad_merchant_id']}";

        }// if live mode
        else{
            $url = rtrim($credentials['nagad_base_url'], '/');
        }

        $response = Http::timeout(60)
            ->acceptJson()
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Nagad create payment failed: ' . $response->body());
        }

        return $response->json();
    }


    public function executePayment(string $paymentId, $credentials = null): array
    {
        $baseUrl = rtrim($credentials['bkash_base_url'], '/');
        return $baseUrl;
    }

    public function queryPayment(string $paymentId, $credentials = null): array
    {

        return 0;

    }


}
