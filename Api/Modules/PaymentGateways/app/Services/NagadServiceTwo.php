<?php

namespace Modules\PaymentGateways\app\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NagadServiceTwo
{
    /**
     * Step 1: Initialize Payment (Send Handshake)
     */
    public function createPayment(array $payload, array $credentials): array
    {
        $merchantId = $credentials['nagad_merchant_id'] ?? '';

        if ($credentials['is_test_mode']) {
            $url = "https://api-sandbox.mynagad.com/api/dfs/check-out/initialize/{$merchantId}";
        } else {
            $baseUrl = rtrim($credentials['nagad_base_url'], '/');
            $url = "{$baseUrl}/api/dfs/check-out/initialize/{$merchantId}";
        }

        $response = Http::timeout(60)
            ->acceptJson()
            ->withHeaders([
                'X-KM-Api-Version' => 'v-0.2.0',
                'X-KM-IP-V4'       => request()->ip() ?? '103.205.128.1',
                'X-KM-Client-Type' => 'PC_WEB',
            ])
            ->post($url, $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Nagad initialize payment failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Step 2: Verify/Execute Payment on Callback
     */
    public function verifyPayment(string $paymentRefId, array $credentials): array
    {
        $merchantId = $credentials['nagad_merchant_id'] ?? '';

        if ($credentials['is_test_mode']) {
            $url = "https://api-sandbox.mynagad.com/api/dfs/check-out/verify/{$merchantId}/{$paymentRefId}";
        } else {
            $baseUrl = rtrim($credentials['nagad_base_url'], '/');
            $url = "{$baseUrl}/api/dfs/check-out/verify/{$merchantId}/{$paymentRefId}";
        }

        $response = Http::timeout(60)
            ->acceptJson()
            ->withHeaders([
                'X-KM-Api-Version' => 'v-0.2.0',
                'X-KM-IP-V4'       => request()->ip() ?? '103.205.128.1',
                'X-KM-Client-Type' => 'PC_WEB',
            ])
            ->get($url); // Verify endpoint uses GET handshake pattern

        if (!$response->successful()) {
            throw new RuntimeException('Nagad verification handshake failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Step 3: Query Payment Status explicitly
     */
    public function queryPayment(string $paymentId, array $credentials): array
    {
        // Add your exact verification or status checking path block here if distinct from step 2
        return $this->verifyPayment($paymentId, $credentials);
    }
}