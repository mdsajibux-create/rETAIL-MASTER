<?php

namespace Database\Seeders;

use App\Models\StoreAreaSettingRangeCharge;
use Illuminate\Database\Seeder;

class StoreAreaSettingRangeChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $rangeCharges = [
            [
                "store_area_setting_id" => 1,
                "min_km" => 0,
                "max_km" => 5,
                "charge_amount" => 5,
                "status" => 1
            ],
            [
                "store_area_setting_id" => 1,
                "min_km" => 5,
                "max_km" => 10,
                "charge_amount" => 10,
                "status" => 1
            ],
            [
                "store_area_setting_id" => 1,
                "min_km" => 10,
                "max_km" => 20,
                "charge_amount" => 20,
                "status" => 1
            ],
            [
                "store_area_setting_id" => 1,
                "min_km" => 20,
                "max_km" => 50,
                "charge_amount" => 30,
                "status" => 1
            ]
        ];

        foreach ($rangeCharges as $charge) {
            StoreAreaSettingRangeCharge::create($charge);
        }
    }
}
