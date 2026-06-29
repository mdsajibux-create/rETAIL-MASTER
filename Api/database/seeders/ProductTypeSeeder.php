<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Create Admin Menu Automatically
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'Furniture',
                'type' => ProductType::FURNITURE->value,
                'image' => null,
                'description' => 'All home furniture and decor items',
                'charge_status' => false,
                'charge_name' => 'Processing fee',
                'charge_amount' => 0.0,
                'charge_type' => 'fixed',
                'status' => 1,
            ],
            [
                'name' => 'Flower',
                'type' => ProductType::FLOWER->value,
                'image' => null,
                'description' => 'Fresh flowers and bouquets',
                'charge_status' => false,
                'charge_name' => 'Processing fee',
                'charge_amount' => 0.0,
                'charge_type' => 'percentage',
                'status' => 1,
            ]

        ];

        DB::table('product_types')->insert($types);

    }
}
