<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'activity_scope' => 'store_level',
                'created_at' => '2021-06-27 04:13:00',
                'email' => 'owner@store.com',
                'email_verified_at' => null,
                'first_name' => 'Store Admin',
                'password' => '$2y$10$oSKpyEavNDBqA29RYY1UueFB1Y0hTUXmHqQeJC9lB1gnzoVTHpVV2',
                'remember_token' => null,
                'slug' => 'store-owner',
                'status' => 1,
                'store_owner' => 1,
                'stores' => '[1,2,3,4]',
                'updated_at' => '2023-10-02 06:53:37',
            ],
            [
                'activity_scope' => 'kitchen_level',
                'created_at' => '2021-08-18 10:30:29',
                'email' => 'kitchenx@demo.com',
                'email_verified_at' => null,
                'first_name' => 'Kitchen X',
                'password' => '$2y$10$UVs.WftC2iIdLQsHz9Tbdu7OmUXG3P7wyjHvJqCunyJ7JE8ekyXr.',
                'remember_token' => null,
                'slug' => 'kitchen-x',
                'status' => 1,
                'store_owner' => 0,
                'stores' => null,
                'updated_at' => '2021-08-18 13:17:53',
            ],
            [
                'activity_scope' => 'kitchen_level',
                'created_at' => '2021-08-18 10:30:29',
                'email' => 'kitchen@demo.com',
                'email_verified_at' => null,
                'first_name' => 'Kitchen',
                'password' => '$2y$10$UVs.WftC2iIdLQsHz9Tbdu7OmUXG3P7wyjHvJqCunyJ7JE8ekyXr.',
                'remember_token' => null,
                'slug' => 'kitchen',
                'status' => 1,
                'store_owner' => 0,
                'stores' => null,
                'updated_at' => '2021-08-18 13:17:53',
            ],
            [
                'activity_scope' => 'kitchen_level',
                'created_at' => '2022-03-17 14:15:08',
                'email' => 'kitchen2@demo.com',
                'email_verified_at' => null,
                'first_name' => 'Kitchen 2',
                'password' => '$2y$10$UVs.WftC2iIdLQsHz9Tbdu7OmUXG3P7wyjHvJqCunyJ7JE8ekyXr.',
                'remember_token' => null,
                'slug' => 'kitchen-2',
                'status' => 1,
                'store_owner' => 0,
                'stores' => null,
                'updated_at' => '2022-03-17 14:15:08',
            ],
            [
                'activity_scope' => 'delivery_level',
                'created_at' => '2022-03-17 16:25:39',
                'email' => 'deliveryman@demo.com',
                'email_verified_at' => null,
                'first_name' => 'Delivery Man',
                'password' => Hash::make('12345678'),
                'remember_token' => null,
                'slug' => 'delivery-man',
                'status' => 1,
                'store_owner' => 0,
                'stores' => null,
                'updated_at' => '2022-03-17 16:25:39',
            ],
            [
                'activity_scope' => 'fitting_level',
                'created_at' => '2022-03-17 16:25:39',
                'email' => 'fitterman@demo.com',
                'email_verified_at' => null,
                'first_name' => 'Fitter Man',
                'password' => '$2y$10$UVs.WftC2iIdLQsHz9Tbdu7OmUXG3P7wyjHvJqCunyJ7JE8ekyXr.',
                'remember_token' => null,
                'slug' => 'fitter-man',
                'status' => 1,
                'store_owner' => 0,
                'stores' => null,
                'updated_at' => '2022-03-17 16:25:39',
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['slug' => $user['slug']],  // Check for existing record with the same slug
                $user  // Insert or update with the user data
            );
            if ($user['activity_scope'] === 'delivery_level') {
                DB::table('delivery_men')->insert(
                    [
                        'user_id' => 6,
                        'store_id' => 1, // Change as per your need
                        'vehicle_type_id' => 1, // Change as per your need
                        'area_id' => null,
                        'identification_type' => 'nid',
                        'identification_number' => '123456789',
                        'status' => 'approved',
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

    }
}
