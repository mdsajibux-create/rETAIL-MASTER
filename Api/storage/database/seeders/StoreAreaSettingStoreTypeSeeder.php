<?php

namespace Database\Seeders;

use App\Models\StoreAreaSettingRangeCharge;
use App\Models\StoreAreaSettingStoreType;
use Illuminate\Database\Seeder;

class StoreAreaSettingStoreTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StoreAreaSettingStoreType::create([
            'store_area_setting_id' => 1,
            'store_type_id' => 1,
            'status' => 1
        ]);

        StoreAreaSettingStoreType::create([
            'store_area_setting_id' => 1,
            'store_type_id' => 2,
            'status' => 1
        ]);

        StoreAreaSettingStoreType::create([
            'store_area_setting_id' => 1,
            'store_type_id' => 3,
            'status' => 1
        ]);

    }
}
