<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class
UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array of 100 unit names
        $units = [
            'Kilogram', 'Gram', 'Liter', 'Milliliter', 'Piece',
            'Meter', 'Centimeter', 'Inch', 'Foot', 'Yard',
            'Tonne', 'Ounce', 'Pound', 'Stone', 'Mile',
            'Nautical Mile', 'Kilometer', 'Hectare', 'Acre', 'Decimeter',
            'Micrometer', 'Nanometer', 'Picometer', 'Fathom', 'Rod',
            'Perch', 'Chain', 'Light Year', 'Parsec', 'Furlong',
            'Bushel', 'Gallon', 'Quart', 'Pint', 'Cup',
            'Fluid Ounce', 'Teaspoon', 'Tablespoon', 'Cubic Meter', 'Cubic Centimeter',
            'Cubic Inch', 'Cubic Foot', 'Cubic Yard', 'Deciliter', 'Milliliter',
            'Barrel', 'Bale', 'Box', 'Bundle', 'Canister',
            'Case', 'Carton', 'Container', 'Crate', 'Drum',
            'Dram', 'Dozen', 'Envelope', 'Gallon (UK)', 'Gallon (US)',
            'Gram per cubic centimeter', 'Hogshead', 'Keg', 'Kiloliter', 'Kilogram per liter',
            'Liter per second', 'M3', 'Megaton', 'Microliter', 'Milligram',
            'Millimeter', 'Newton', 'Ounce (Fluid)', 'Ounce (Weight)', 'Packet',
            'Pair', 'Pen', 'Pound per square inch', 'Quart (UK)', 'Quart (US)',
            'Ration', 'Ream', 'Roll', 'Set', 'Sheet',
            'Slab', 'Slice', 'Teaspoon (Metric)', 'Ton (UK)', 'Ton (US)',
            'Troy ounce', 'Vial', 'Watt', 'Watt-hour', 'Yottabyte',
            'Zettabyte', 'Kibibyte', 'Mebibyte', 'Gibibyte', 'Tebibyte',
            'Petabyte', 'Exabyte', 'Kilobit', 'Megabit', 'Gigabit',
            'Terabit', 'Petabit', 'Exabit', 'Zettabit', 'Yottabit'
        ];

        // Loop through the units and insert them with an order
        foreach ($units as $index => $unit) {
            DB::table('units')->insert([
                'name' => $unit,
                'order' => $index + 1,  // Order starts from 1 and increments
            ]);
        }



    }
}
