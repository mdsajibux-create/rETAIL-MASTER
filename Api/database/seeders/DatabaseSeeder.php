<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(ModelHasRolesSeeder::class);
        $this->call(PermissionAdminSeeder::class);
        $this->call(PermissionBranchSeeder::class);
        $this->call(PermissionDeliverymanSeeder::class);
        $this->call(SystemChargesSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(ProductTypeSeeder::class);
    }
}
