<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class
SystemCommissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('system_commissions')->insert([
            [
                'subscription_enabled' => 1,
                'commission_enabled' => 1,
                'commission_type' => 'commission',
                'commission_amount' => 10.00,
                'default_order_commission_rate' => 1.00,
                'default_delivery_commission_charge' => 1.00,
                'order_shipping_charge' => 1.00,
                'order_confirmation_by' => "store",
                'order_include_tax_amount' => true,
                'order_additional_charge_enable_disable' => true,
                'order_additional_charge_name' => "Processing Fee",
                'order_additional_charge_amount' => 5.00,
            ]
        ]);



    }
}
