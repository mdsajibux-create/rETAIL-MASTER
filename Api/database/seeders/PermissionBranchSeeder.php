<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Permission as ModelsPermission;
use Spatie\Permission\Models\Role;

class PermissionBranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionIds = DB::table('permissions')
            ->where('available_for', 'branch_level')
            ->pluck('id')
            ->toArray();

        if (!empty($permissionIds)) {
            // Delete permissions
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
            // Delete related translations
            DB::table('translations')->whereIn('translatable_id', $permissionIds)
                ->where('translatable_type', 'App\\Models\\Permissions')
                ->delete();
        }

        $admin_main_menu = [];
        $shop_menu = [
            [
                // Dashboard
                [
                    'PermissionName' => 'dashboard',
                    'PermissionTitle' => 'Dashboard',
                    'activity_scope' => 'branch_level',
                    'icon' => 'LayoutDashboard',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'Dashboard',
                        'ar' => 'قائمة المناطق'
                    ]
                ],

                // POS
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'POS Management',
                    'activity_scope' => 'branch_level',
                    'icon' => 'CircleDollarSign',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'POS Management',
                        'ar' => 'نقاط البيع'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'POS',
                            'activity_scope' => 'branch_level',
                            'icon' => 'ListOrdered',
                            'options' => ['view',  'insert', 'update', 'delete', 'others'],
                            'translations' => [
                                'en' => 'POS',
                                'ar' => 'المبيعات'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::BRANCH_POS_SALES->value,
                                    'PermissionTitle' => 'POS',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'ListOrdered',
                                    'options' => ['view', 'insert', 'update', 'delete', 'others'],
                                    'translations' => [
                                        'en' => 'POS',
                                        'ar' => 'المبيعات'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::BRANCH_POS_ORDERS->value,
                                    'PermissionTitle' => 'Orders',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'ListOrdered',
                                    'options' => ['view', 'insert', 'update', 'delete', 'others'],
                                    'translations' => [
                                        'en' => 'Orders',
                                        'ar' => 'طلبات'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],


                // order manage
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Order Management',
                    'activity_scope' => 'branch_level',
                    'icon' => '',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'Orders & Reviews',
                        'ar' => 'قائمة المناطق'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'Orders',
                            'activity_scope' => 'branch_level',
                            'icon' => 'Boxes',
                            'options' => ['view',  'insert', 'update', 'others'],
                            'translations' => [
                                'en' => 'Orders',
                                'ar' => 'قائمة المناطق'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::BRANCH_ORDER_MANAGE->value,
                                    'PermissionTitle' => 'All Orders',
                                    'activity_scope' => 'branch_level',
                                    'options' => ['view',  'insert', 'update', 'others'],
                                    'icon' => '',
                                    'translations' => [
                                        'en' => 'All Orders',
                                        'ar' => 'جميع الطلبات'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_ORDERS_RETURNED_OR_REFUND_REQUEST->value,
                                    'PermissionTitle' => 'Returned or Refunded',
                                    'activity_scope' => 'branch_level',
                                    'icon' => '',
                                    'options' => ['view'],
                                    'translations' => [
                                        'en' => 'Returned or Refunded',
                                        'ar' => 'تم إرجاعه أو استرداده'
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],

                // Product Management
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Product management',
                    'activity_scope' => 'branch_level',
                    'icon' => '',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'Product management',
                        'ar' => 'قائمة المناطق'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'Products',
                            'activity_scope' => 'branch_level',
                            'icon' => 'Codesandbox',
                            'options' => ['view',  'insert', 'update', 'others'],
                            'translations' => [
                                'en' => 'Products',
                                'ar' => 'منتجات'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::BRANCH_PRODUCT_LIST->value,
                                    'PermissionTitle' => 'Product List',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'PackageOpen',
                                    'options' => ['view',  'insert', 'update', 'others'],
                                    'translations' => [
                                        'en' => 'Product List',
                                        'ar' => 'قائمة المنتجات'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::BRANCH_PRODUCT_STOCK_MANAGE->value,
                                    'PermissionTitle' => 'Stock Management',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'PackageOpen',
                                    'options' => ['view',  'insert', 'update', 'others'],
                                    'translations' => [
                                        'en' => 'Stock Management',
                                        'ar' => 'إدارة المخزون'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::BRANCH_PRODUCT_STOCK_TRANSFER_MANAGE->value,
                                    'PermissionTitle' => 'Stock Transfer',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'PackageOpen',
                                    'options' => ['view',  'insert', 'update', 'others'],
                                    'translations' => [
                                        'en' => 'Stock Transfer',
                                        'ar' => 'إدارة المخزون'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::BRANCH_PRODUCT_INVENTORY->value,
                                    'PermissionTitle' => 'Inventory',
                                    'activity_scope' => 'branch_level',
                                    'icon' => 'PackageOpen',
                                    'options' => ['view',  'insert', 'update', 'others'],
                                    'translations' => [
                                        'en' => 'Inventory',
                                        'ar' => 'السحوبات'
                                    ]
                                ],

                            ]
                        ],
                    ]
                ],


                // Communication Center
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Communication Center',
                    'activity_scope' => 'branch_level',
                    'icon' => 'Headphones',
                    'options' => ['view', 'insert', 'update', 'delete', 'others'],
                    'translations' => [
                        'en' => 'Communication Center',
                        'ar' => 'مركز الاتصالات'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_CHAT_MANAGE->value,
                            'PermissionTitle' => 'Chat List',
                            'activity_scope' => 'branch_level',
                            'icon' => 'MessageSquareMore',
                            'options' => ['view', 'insert', 'update'],
                            'translations' => [
                                'en' => 'Chat',
                                'ar' => 'إعدادات الدردشة'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_SUPPORT_TICKETS_MANAGE->value,
                            'PermissionTitle' => 'Tickets',
                            'activity_scope' => 'branch_level',
                            'icon' => 'Headset',
                            'options' => ['view', 'insert', 'update', 'delete', 'others'],
                            'translations' => [
                                'en' => 'Support Ticket',
                                'ar' => 'السحوبات'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::BRANCH_NOTIFICATION_MANAGEMENT->value,
                            'PermissionTitle' => 'All Notifications',
                            'activity_scope' => 'branch_level',
                            'icon' => 'Bell',
                            'translations' => [
                                'en' => 'All Notifications',
                                'ar' => 'كل الإشعارات'
                            ]
                        ]
                    ]
                ],

                // Staff control
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Staff control',
                    'activity_scope' => 'branch_level',
                    'icon' => 'UserRoundPen',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'Staff control',
                        'ar' => 'التحكم بالمستخدم'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::BRANCH_STAFF_MANAGE->value,
                            'PermissionTitle' => 'Staff List',
                            'activity_scope' => 'branch_level',
                            'icon' => 'Users',
                            'options' => ['view', 'insert', 'update', 'delete'],
                            'translations' => [
                                'en' => 'Staff List',
                                'ar' => 'قائمة'
                            ]
                        ],

                    ]
                ],

            ]
        ];


        $page_list = array_merge($admin_main_menu, $shop_menu);

        foreach ($page_list as $x_mod) {
            foreach ($x_mod as $level_1) {

                $trans_level_1 = [];
                $options_l1 = isset($level_1['options']) && is_array($level_1['options']) ? $level_1['options'] : ['view'];

                $permission_l1 = ModelsPermission::updateOrCreate(
                    [
                        'name' => $level_1['PermissionName'] != '' ? $level_1['PermissionName'] : $level_1['PermissionTitle'],
                        'perm_title' => $level_1['PermissionTitle'],
                        'guard_name' => 'api',
                        'icon' => $level_1['icon'],
                        'available_for' => $level_1['activity_scope'],
                        'options' => json_encode($options_l1)
                    ]
                );
                foreach ($level_1['translations'] as $key => $value) {
                    $trans_level_1[] = [
                        'translatable_type' => 'App\Models\Permissions',
                        'translatable_id' => $permission_l1->id,
                        'language' => $key,
                        'key' => 'perm_title',
                        'value' => $value,
                    ];
                }
                Translation::insert($trans_level_1);

                // Level 2 Menu Insert
                if (isset($level_1['submenu']) && is_array($level_1['submenu'])) {
                    foreach ($level_1['submenu'] as $level_2) {

                        $trans_level_2 = [];
                        $options_l2 = isset($level_2['options']) && is_array($level_2['options']) ? $level_2['options'] : ['view'];

                        $permission_l2 = ModelsPermission::updateOrCreate(
                            [
                                'name' => $level_2['PermissionName'] != '' ? $level_2['PermissionName'] : $level_2['PermissionTitle'],
                                'perm_title' => $level_2['PermissionTitle'],
                                'guard_name' => 'api',
                                'icon' => $level_2['icon'],
                                'available_for' => $level_2['activity_scope'],
                                'options' => json_encode($options_l2),
                                'parent_id' => $permission_l1->id
                            ]
                        );
                        foreach ($level_2['translations'] as $key_2 => $value_2) {
                            $trans_level_2[] = [
                                'translatable_type' => 'App\Models\Permissions',
                                'translatable_id' => $permission_l2->id,
                                'language' => $key_2,
                                'key' => 'perm_title',
                                'value' => $value_2,
                            ];
                        }
                        Translation::insert($trans_level_2);

                        // Level 3 Menu Insert
                        if (isset($level_2['submenu']) && is_array($level_2['submenu'])) {
                            foreach ($level_2['submenu'] as $level_3) {

                                $trans_level_3 = [];
                                $options_l3 = isset($level_3['options']) && is_array($level_3['options']) ? $level_3['options'] : ['view'];

                                $permission_l3 = ModelsPermission::updateOrCreate(
                                    [
                                        'name' => $level_3['PermissionName'],
                                        'perm_title' => $level_3['PermissionTitle'],
                                        'guard_name' => 'api',
                                        'icon' => $level_3['icon'],
                                        'available_for' => $level_3['activity_scope'],
                                        'options' => json_encode($options_l3),
                                        'parent_id' => $permission_l2->id
                                    ]
                                );
                                foreach ($level_3['translations'] as $key_3 => $value_3) {
                                    $trans_level_3[] = [
                                        'translatable_type' => 'App\Models\Permissions',
                                        'translatable_id' => $permission_l3->id,
                                        'language' => $key_3,
                                        'key' => 'perm_title',
                                        'value' => $value_3,
                                    ];
                                }
                                Translation::insert($trans_level_3);

                            }
                        }
                    }
                }
            }
        }


        //Assign PermissionKey to Store Admin Role
        $role = Role::where('id', 2)->first();
        $role->givePermissionTo(Permission::whereIn('available_for', ['branch_level', 'COMMON'])->get());
        $user = User::whereEmail('branch@gmail.com')->first();
        if (!$user) {
            return;
        }
        // Assign default Store User to a Specific Role
        $user->assignRole($role);
    }
}
