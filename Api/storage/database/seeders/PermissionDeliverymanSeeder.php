<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as ModelsPermission;

class PermissionDeliverymanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $deliverymanPermissions = [
            [
                'PermissionName' => PermissionKey::DELIVERYMAN_FINANCIAL_WITHDRAWALS->value,
                'PermissionTitle' => 'Withdrawals',
                'activity_scope' => 'delivery_level',
                'icon' => '',
                'options' => ['view', 'insert', 'others'],
                'translations' => [
                    'en' => 'Withdrawals',
                    'ar' => 'السحوبات'
                ],
            ]
        ];

        $this->insertPermissions($deliverymanPermissions);
    }

    /**
     * Insert permissions recursively.
     *
     * @param array $permissions
     * @param int|null $parentId
     * @return void
     */
    private function insertPermissions(array $permissions, $parentId = null)
    {
        foreach ($permissions as $permissionData) {
            $options = isset($permissionData['options']) && is_array($permissionData['options']) ? $permissionData['options'] : ['view'];

            // Create the permission
            $permission = ModelsPermission::updateOrCreate(
                [
                    'name' => $permissionData['PermissionName'],
                    'perm_title' => $permissionData['PermissionTitle'],
                    'guard_name' => 'api',
                    'icon' => $permissionData['icon'],
                    'available_for' => $permissionData['activity_scope'],
                    'options' => json_encode($options),
                    'parent_id' => $parentId
                ]
            );

            // Insert translations
            $translations = [];
            foreach ($permissionData['translations'] as $key => $value) {
                $translations[] = [
                    'translatable_type' => 'App\Models\Permissions',
                    'translatable_id' => $permission->id,
                    'language' => $key,
                    'key' => 'perm_title',
                    'value' => $value,
                ];
            }
            Translation::insert($translations);

            // Recursively insert submenu permissions
            if (!empty($permissionData['submenu'])) {
                $this->insertPermissions($permissionData['submenu'], $permission->id);
            }
        }
    }
}
