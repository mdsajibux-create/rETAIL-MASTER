<?php

namespace Modules\PaymentGateways\app\Services;


use Illuminate\Support\Facades\Http;
use Modules\PaymentGateways\app\Models\PaymentGateway;


class SSLCommerzService
{
    private string $storeId;
    private string $storePassword;
    private string $initUrl;
    private string $validateUrl;
    private bool   $isProduction;

    public function __construct()
    {
        // payment gateway settings from database
        $gateway     = PaymentGateway::where('slug', 'sslcommerz')->firstOrFail();
        $credentials = $gateway->auth_credentials
            ? json_decode($gateway->auth_credentials, true)
            : [];

        //  is_test_mode true = sandbox (NOT production)
        $this->isProduction  = ! (bool) ($gateway->is_test_mode ?? true);
        $this->storeId       = $credentials['sslcommerz_store_id']       ?? '';
        $this->storePassword = $credentials['sslcommerz_store_password']  ?? '';

        $base = $this->isProduction
            ? 'https://securepay.sslcommerz.com'
            : 'https://sandbox.sslcommerz.com';

        $this->initUrl     = $base . '/gwprocess/v4/api.php';
        $this->validateUrl = $base . '/validator/api/validationserverAPI.php';
    }

    public function initiatePayment(array $postData): array
    {

        $postData['store_id']     = $this->storeId;
        $postData['store_passwd'] = $this->storePassword;

        $response = Http::asForm()
            ->timeout(30)
            ->post($this->initUrl, $postData);

        if ($response->failed()) {
            throw new \Exception('SSLCommerz connection failed.');
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            throw new \Exception($data['failedreason'] ?? 'Payment initiation failed.');
        }

        return $data;
    }

    public function validatePayment(string $valId): array
    {
        $response = Http::timeout(30)->get($this->validateUrl, [
            'val_id'       => $valId,
            'store_id'     => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format'       => 'json',
        ]);

        if ($response->failed()) {
            throw new \Exception('SSLCommerz validation request failed.');
        }

        return $response->json();
    }



    public function verifyIpnHash(array $requestData): bool
    {
        if (empty($requestData['verify_sign']) || empty($requestData['verify_key'])) {
            return false;
        }

        $keys = explode(',', $requestData['verify_key']);

        $data = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $requestData)) {
                $data[$key] = $requestData[$key];
            }
        }

        $data['store_passwd'] = md5($this->storePassword);

        ksort($data);

        $hashString = '';
        foreach ($data as $key => $value) {
            $hashString .= $key . '=' . $value . '&';
        }
        $hashString = rtrim($hashString, '&');

        $generatedSign = md5($hashString);

        $ok = hash_equals(
            strtolower($requestData['verify_sign']),
            strtolower($generatedSign)
        );


        if (! $ok) {
          return false;
        }

        return $ok;
    }




}
