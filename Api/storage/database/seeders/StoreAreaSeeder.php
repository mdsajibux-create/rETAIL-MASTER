<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreAreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $areas = [
            [
                'code' => 'BD-001',
                'state' => 'Dhaka',
                'city' => 'Dhaka City',
                'name' => 'Gulshan',
                'coordinates' => "POLYGON((90.402992 23.795349, 90.405081 23.796130, 90.406190 23.794773, 90.407000 23.793232, 90.402992 23.795349))",
            ],
            [
                'code' => 'NY-002',
                'state' => 'New York',
                'city' => 'Brooklyn',
                'name' => 'Brooklyn',
                'coordinates' => "POLYGON((-73.940428 40.650817, -73.945428 40.655818, -73.950429 40.660819, -73.955429 40.665820, -73.940428 40.650817))",
            ],
            [
                'code' => 'NY-003',
                'state' => 'New York',
                'city' => 'Queens',
                'name' => 'Queens',
                'coordinates' => "POLYGON((-73.799428 40.721817, -73.804428 40.725818, -73.809429 40.730819, -73.814429 40.735820, -73.799428 40.721817))",
            ],
            [
                'code' => 'CA-001',
                'state' => 'California',
                'city' => 'Los Angeles',
                'name' => 'Downtown LA',
                'coordinates' => "POLYGON((-118.251928 34.050817, -118.256928 34.055818, -118.261929 34.060819, -118.266929 34.065820, -118.251928 34.050817))",
            ],
            [
                'code' => 'CA-002',
                'state' => 'California',
                'city' => 'San Francisco',
                'name' => 'San Francisco',
                'coordinates' => "POLYGON((-122.419428 37.774817, -122.424428 37.779818, -122.429429 37.784819, -122.434429 37.789820, -122.419428 37.774817))",
            ],

        ];

        foreach ($areas as $area) {
            DB::table('store_areas')->updateOrInsert(
                ['code' => $area['code']],
                [
                    'state' => $area['state'],
                    'city' => $area['city'],
                    'name' => $area['name'],
                    'coordinates' => DB::raw("ST_GeomFromText('{$area['coordinates']}')"),
                    'center_latitude' => DB::raw("ST_Y(ST_Centroid(ST_GeomFromText('{$area['coordinates']}')))"),
                    'center_longitude' => DB::raw("ST_X(ST_Centroid(ST_GeomFromText('{$area['coordinates']}')))"),
                    'status' => 1,
                    'created_by' => null,
                    'updated_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
