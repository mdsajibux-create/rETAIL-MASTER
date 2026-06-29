<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\PaymentGateways\app\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::create([
            'name' => 'PayPal',
            'slug' => 'paypal',
            'description' => 'fdf',
            'auth_credentials' => json_encode([
                'paypal_sandbox_client_id' => '',
                'paypal_sandbox_client_secret' => '',
                'paypal_sandbox_client_app_id' => '',
                'paypal_live_client_id' => '',
                'paypal_live_client_secret' => '',
                'paypal_live_client_app_id' => '',
            ]),
            'image' => null,
            'status' => true,
            'is_test_mode' => true,
        ]);

        PaymentGateway::create([
            'name' => 'Stripe',
            'slug' => 'stripe',
            'description' => 'stripe info',
            'auth_credentials' => json_encode([
                'stripe_public_key' => '',
                'stripe_public_secret' => '',
            ]),
            'image' => null,
            'status' => true,
            'is_test_mode' => true,
        ]);

        PaymentGateway::create([
            'name' => 'Razorpay',
            'slug' => 'razorpay',
            'description' => 'razorpay info',
            'auth_credentials' => json_encode([
                'razorpay_api_key' => '',
                'razorpay_api_secret' => '',
            ]),
            'image' => null,
            'status' => true,
            'is_test_mode' => true,
        ]);

        PaymentGateway::create([
            'name' => 'Paytm',
            'slug' => 'paytm',
            'description' => 'paytm info',
            'auth_credentials' => json_encode([
                'paytm_seller_key' => '',
                'paytm_seller_mid' => '',
                'paytm_seller_website' => '',
                'paytm_cancel_url' => '',
                'paytm_industry_type' => '',
            ]),
            'image' => null,
            'status' => true,
            'is_test_mode' => true,
        ]);

        PaymentGateway::create([
            'name' => 'Cash On Delivery',
            'slug' => 'cash_on_delivery',
            'description' => 'Pay for your order in cash when it is delivered to your doorstep. No online payment required!',
            'auth_credentials' => null,
            'image' => null,
            'status' => true,
            'is_test_mode' => true,
        ]);
    }
}
