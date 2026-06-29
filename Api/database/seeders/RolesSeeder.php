<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Create Admin Menu Automatically
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'Super Admin', 'guard_name' => 'api'],
            [
                'available_for' => 'system_level',
                'name' => 'Super Admin',
                'guard_name' => 'api',
                'locked' => true,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Branch Admin', 'guard_name' => 'api'],
            [
                'available_for' => 'branch_level',
                'name' => 'Branch Admin',
                'guard_name' => 'api',
                'locked' => true,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Delivery Man', 'guard_name' => 'api'],
            [
                'available_for' => 'delivery_level',
                'name' => 'Delivery Man',
                'guard_name' => 'api',
                'locked' => true,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

    }
}
