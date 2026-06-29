<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission as ModelsPermission;

class ModelHasRolesSeeder extends Seeder
{
    /**
     * Create Admin Menu Automatically
     *
     * @return void
     */
    public function run()
    {
        DB::table('model_has_roles')->updateOrInsert(
            ['model_id' => 1, 'model_type' => 'App\\Models\\User', 'role_id' => 2],
            ['model_id' => 1, 'model_type' => 'App\\Models\\User', 'role_id' => 2]
        );

        DB::table('model_has_roles')->updateOrInsert(
            ['model_id' => 2, 'model_type' => 'App\\Models\\User', 'role_id' => 3],
            ['model_id' => 2, 'model_type' => 'App\\Models\\User', 'role_id' => 3]
        );

        DB::table('model_has_roles')->updateOrInsert(
            ['model_id' => 6, 'model_type' => 'App\\Models\\User', 'role_id' => 4],
            ['model_id' => 6, 'model_type' => 'App\\Models\\User', 'role_id' => 4]
        );

        DB::table('model_has_roles')->updateOrInsert(
            ['model_id' => 7, 'model_type' => 'App\\Models\\User', 'role_id' => 5],
            ['model_id' => 7, 'model_type' => 'App\\Models\\User', 'role_id' => 5]
        );
    }
}
