<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Subscription\app\Models\Subscription;

class SubscriptionPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                "name" => "Trial Package",
                "type" => 'Weekly',
                "validity" => 30,
                "price" => 0,
                "pos_system" => false,
                "self_delivery" => false,
                "mobile_app" => false,
                "live_chat" => false,
                "order_limit" => 10,
                "product_limit" => 10,
                "product_featured_limit" => 2
            ],
            [
                "name" => "Basic Package",
                "type" => 'Monthly',
                "validity" => 30,
                "price" => 30,
                "pos_system" => true,
                "self_delivery" => false,
                "mobile_app" => false,
                "live_chat" => false,
                "order_limit" => 50,
                "product_limit" => 50,
                "product_featured_limit" => 5
            ],
            [
                "name" => "Standard Package",
                "type" => 'Half-Yearly',
                "validity" => 180,
                "price" => 100,
                "pos_system" => true,
                "self_delivery" => true,
                "mobile_app" => false,
                "live_chat" => true,
                "order_limit" => 100,
                "product_limit" => 150,
                "product_featured_limit" => 10
            ],
            [
                "name" => "Premium Package",
                "type" => 'Yearly',
                "validity" => 365,
                "price" => 200,
                "pos_system" => true,
                "self_delivery" => true,
                "mobile_app" => true,
                "live_chat" => true,
                "order_limit" => 500,
                "product_limit" => 200,
                "product_featured_limit" => 15
            ],
            [
                "name" => "Enterprise Package",
                "type" => 'Extended',
                "validity" => 1095,
                "price" => 500,
                "pos_system" => true,
                "self_delivery" => true,
                "mobile_app" => true,
                "live_chat" => true,
                "order_limit" => 1000,
                "product_limit" => 500,
                "product_featured_limit" => 25
            ]
        ];

        foreach ($packages as $package) {
            Subscription::create($package);
        }


    }
}
