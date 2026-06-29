<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $states = [
            'USA' => [
                'California', 'Texas', 'New York', 'Florida', 'Illinois', 'Pennsylvania', 'Ohio', 'Georgia', 'North Carolina', 'Michigan',
                'New Jersey', 'Virginia', 'Washington', 'Arizona', 'Massachusetts', 'Tennessee', 'Indiana', 'Missouri', 'Maryland', 'Wisconsin'
            ],
            'India' => [
                'Maharashtra', 'Karnataka', 'Delhi', 'Tamil Nadu', 'West Bengal', 'Uttar Pradesh', 'Gujarat', 'Rajasthan', 'Madhya Pradesh', 'Bihar',
                'Telangana', 'Punjab', 'Haryana', 'Kerala', 'Odisha', 'Assam', 'Jharkhand', 'Chhattisgarh', 'Uttarakhand', 'Himachal Pradesh'
            ],
            'Canada' => [
                'Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba', 'Saskatchewan', 'Nova Scotia', 'New Brunswick', 'Newfoundland and Labrador', 'Prince Edward Island'
            ],
            'Australia' => [
                'New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia', 'Tasmania', 'Australian Capital Territory', 'Northern Territory'
            ],
            'United Kingdom' => [
                'England', 'Scotland', 'Wales', 'Northern Ireland'
            ],
            'Germany' => [
                'Bavaria', 'Baden-Württemberg', 'North Rhine-Westphalia', 'Hesse', 'Lower Saxony', 'Rhineland-Palatinate', 'Saxony', 'Berlin', 'Hamburg', 'Brandenburg'
            ],
            'France' => [
                'Île-de-France', 'Provence-Alpes-Côte d\'Azur', 'Occitanie', 'Nouvelle-Aquitaine', 'Grand Est', 'Auvergne-Rhône-Alpes', 'Brittany', 'Normandy', 'Corsica'
            ],
            'Brazil' => [
                'São Paulo', 'Rio de Janeiro', 'Minas Gerais', 'Bahia', 'Paraná', 'Rio Grande do Sul', 'Pernambuco', 'Ceará', 'Amazonas', 'Santa Catarina'
            ],
            'China' => [
                'Beijing', 'Shanghai', 'Guangdong', 'Sichuan', 'Henan', 'Jiangsu', 'Shandong', 'Zhejiang', 'Hubei', 'Fujian'
            ],
            'Russia' => [
                'Moscow', 'Saint Petersburg', 'Novosibirsk', 'Yekaterinburg', 'Kazan', 'Chelyabinsk', 'Omsk', 'Samara', 'Rostov-on-Don', 'Ufa'
            ],
            'Japan' => [
                'Tokyo', 'Osaka', 'Kyoto', 'Hokkaido', 'Fukuoka', 'Aichi', 'Hyogo', 'Hiroshima', 'Okinawa', 'Nagano'
            ],
            'Mexico' => [
                'Mexico City', 'Jalisco', 'Nuevo León', 'Puebla', 'Yucatán', 'Chihuahua', 'Sonora', 'Tamaulipas', 'Querétaro', 'Coahuila'
            ],
            'South Africa' => [
                'Gauteng', 'Western Cape', 'KwaZulu-Natal', 'Eastern Cape', 'Free State', 'Limpopo', 'Mpumalanga', 'Northern Cape', 'North West'
            ],
            'Italy' => [
                'Lombardy', 'Lazio', 'Campania', 'Sicily', 'Veneto', 'Tuscany', 'Emilia-Romagna', 'Piedmont', 'Apulia', 'Sardinia'
            ],
            'Spain' => [
                'Andalusia', 'Catalonia', 'Madrid', 'Valencia', 'Galicia', 'Castile and León', 'Basque Country', 'Canary Islands', 'Murcia', 'Aragon'
            ]
        ];
        $region = $faker->randomElement(array_keys($states)); // Select a random country
        $state = $faker->randomElement($states[$region]); // Select a random state from that country

        // Seed Countries (100 entries)
        $countries = [];
        for ($i = 1; $i <= 100; $i++) {
            $countries[] = [
                'name' => $faker->country,
                'code' => strtoupper($faker->unique()->lexify('??')),
                'dial_code' => '+' . $faker->randomNumber(2),
                'latitude' => $faker->latitude,
                'longitude' => $faker->longitude,
                'timezone' => $faker->timezone,
                'region' => $region,
                'languages' => implode(', ', $faker->randomElements(['English', 'Spanish', 'French', 'German', 'Chinese'], rand(1, 3))),
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('countries')->insert($countries);

        // Fetch inserted country IDs
        $countryIds = DB::table('countries')->pluck('id')->toArray();

        // Seed States (100 entries)
        $states = [];
        for ($i = 1; $i <= 100; $i++) {
            $states[] = [
                'name' =>  $state,
                'country_id' => $faker->randomElement($countryIds),
                'timezone' => $faker->timezone,
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('states')->insert($states);

        // Fetch inserted state IDs
        $stateIds = DB::table('states')->pluck('id')->toArray();

        // Seed Cities (100 entries)
        $cities = [];
        for ($i = 1; $i <= 100; $i++) {
            $cities[] = [
                'name' => $faker->city,
                'state_id' => $faker->randomElement($stateIds),
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('cities')->insert($cities);

        // Fetch inserted city IDs
        $cityIds = DB::table('cities')->pluck('id')->toArray();

        // Seed Areas (100 entries)
        $areas = [];
        for ($i = 1; $i <= 100; $i++) {
            $areas[] = [
                'name' => $faker->streetName,
                'city_id' => $faker->randomElement($cityIds),
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('areas')->insert($areas);

        $this->command->info('Country, State, City, and Area tables seeded successfully!');
    }
}
