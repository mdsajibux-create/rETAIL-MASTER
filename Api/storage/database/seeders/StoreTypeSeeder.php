<?php

namespace Database\Seeders;

use App\Models\StoreType;
use Illuminate\Database\Seeder;

class StoreTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storeTypes = [
            ['name' => 'Grocery', 'type' => \App\Enums\ProductType::GROCERY->value],
            ['name' => 'Bakery', 'type' => \App\Enums\ProductType::BAKERY->value],
            ['name' => 'Medicine', 'type' => \App\Enums\ProductType::MEDICINE->value],
            ['name' => 'Makeup', 'type' => \App\Enums\ProductType::MAKEUP->value],
            ['name' => 'Bags', 'type' => \App\Enums\ProductType::BAGS->value],
            ['name' => 'Clothing', 'type' => \App\Enums\ProductType::CLOTHING->value],
            ['name' => 'Furniture', 'type' => \App\Enums\ProductType::FURNITURE->value],
            ['name' => 'Books', 'type' => \App\Enums\ProductType::BOOKS->value],
            ['name' => 'Gadgets', 'type' => \App\Enums\ProductType::GADGET->value],
            ['name' => 'Animals & Pets', 'type' => \App\Enums\ProductType::ANIMALS_PET->value],
            ['name' => 'Fish', 'type' => \App\Enums\ProductType::FISH->value],
        ];

        foreach ($storeTypes as $storeType) {
            StoreType::updateOrInsert(
                ['type' => $storeType['type']], // Unique column to check
                [
                    'name' => $storeType['name'],
                    'status' => 1
                ]
            );
        }

    }
}
