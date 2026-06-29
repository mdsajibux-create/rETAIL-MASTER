<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemChargesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_charges')->insert([
            [
                'order_shipping_charge' => 60,
                'order_confirmation_by' => 'deliveryman',
                'order_include_tax_amount' => true,
                'order_tax' => 5,
                'order_additional_charge_enable_disable' => true,
                'order_additional_charge_name' => "Processing Fee",
                'order_additional_charge_amount' => 5.00,
                'deliveryman_earning_type' => 'commission',
                'deliveryman_commission_type' => 'percentage',
                'deliveryman_commission_value' => 5,
            ]
        ]);
    }
}
