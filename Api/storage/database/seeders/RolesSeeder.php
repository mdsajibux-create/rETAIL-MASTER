<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission as ModelsPermission;

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
            ['name' => 'Store Admin', 'guard_name' => 'api'],
            [
                'available_for' => 'store_level',
                'name' => 'Store Admin',
                'guard_name' => 'api',
                'locked' => true,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Store Manager', 'guard_name' => 'api'],
            [
                'available_for' => 'store_level',
                'name' => 'Store Manager',
                'guard_name' => 'api',
                'locked' => false,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Store Officer', 'guard_name' => 'api'],
            [
                'available_for' => 'store_level',
                'name' => 'Store Officer',
                'guard_name' => 'api',
                'locked' => false,
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

        DB::table('roles')->updateOrInsert(
            ['name' => 'Fitter Man', 'guard_name' => 'api'],
            [
                'available_for' => 'fitting_level',
                'name' => 'Fitter Man',
                'guard_name' => 'api',
                'locked' => false,
                'created_at' => '2023-08-11 11:57:33',
                'updated_at' => '2023-08-11 11:57:33',
            ]
        );

    }
}
