<?php

namespace Database\Seeders;

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
                'activity_scope' => 'branch_level',
                'created_at' => '2021-06-27 04:13:00',
                'email' => 'branch@gmail.com',
                'email_verified_at' => null,
                'first_name' => 'Branch Admin',
                'password' => '$2y$10$oSKpyEavNDBqA29RYY1UueFB1Y0hTUXmHqQeJC9lB1gnzoVTHpVV2',
                'remember_token' => null,
                'slug' => 'branch-admin',
                'status' => 1,
                'branch_id' => 1,
                'updated_at' => '2023-10-02 06:53:37',
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
                'branch_id' => null,
                'updated_at' => '2022-03-17 16:25:39',
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['slug' => $user['slug']],
                $user
            );
            if ($user['activity_scope'] === 'delivery_level') {
                DB::table('delivery_men')->insert(
                    [
                        'user_id' => 6,
                        'vehicle_type_id' => 1,
                        'zone_id' => null,
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
