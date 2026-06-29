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

class PermissionStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->where('available_for','store_level')->delete();
        $admin_main_menu = [];
        $shop_menu = [
            [
                // Dashboard
                [
//                    'PermissionName' => PermissionKey::SELLER_DASHBOARD->value,
                    'PermissionName' => 'dashboard',
                    'PermissionTitle' => 'Dashboard',
                    'activity_scope' => 'store_level',
                    'icon' => 'LayoutDashboard',
                    'options' => ['view'],
                    'translations' => [
                        'en' => 'Dashboard',
                        'ar' => 'قائمة المناطق'
                    ]
                ],
//                [
//                    'PermissionName' => PermissionKey::SELLER_STORE_DASHBOARD->value,
//                    'PermissionTitle' => 'Store Dashboard',
//                    'activity_scope' => 'store_level',
//                    'icon' => 'LayoutDashboard',
//                    'options' => ['view'],
//                    'translations' => [
//                        'en' => 'Dashboard',
//                        'ar' => 'قائمة المناطق'
//                    ]
//                ],

                // order manage
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Orders & Reviews',
                    'activity_scope' => 'store_level',
                    'icon' => '',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Orders & Reviews',
                        'ar' => 'قائمة المناطق'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'Orders',
                            'activity_scope' => 'store_level',
                            'icon' => 'Boxes',
                            'options' => ['View'],
                            'translations' => [
                                'en' => 'Orders',
                                'ar' => 'قائمة المناطق'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_ORDER_MANAGE->value,
                                    'PermissionTitle' => 'All Orders',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'translations' => [
                                        'en' => 'All Orders',
                                        'ar' => 'جميع الطلبات'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_ORDERS_RETURNED_OR_REFUND_REQUEST->value,
                                    'PermissionTitle' => 'Returned or Refunded',
                                    'activity_scope' => 'store_level',
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

                // product manage
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Product management',
                    'activity_scope' => 'store_level',
                    'icon' => '',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Product management',
                        'ar' => 'قائمة المناطق'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'Products',
                            'activity_scope' => 'store_level',
                            'icon' => 'Codesandbox',
                            'options' => ['View'],
                            'translations' => [
                                'en' => 'Products',
                                'ar' => 'منتجات'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_LIST->value,
                                    'PermissionTitle' => 'Manage Products',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view', 'insert', 'update', 'delete', 'others'],
                                    'translations' => [
                                        'en' => 'Manage Products',
                                        'ar' => 'إدارة المنتجات'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_ADD->value,
                                    'PermissionTitle' => 'Add New Product',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view', 'insert', 'update', 'delete'],
                                    'translations' => [
                                        'en' => 'Add New Product',
                                        'ar' => 'إضافة منتج جديد'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_STOCK_REPORT->value,
                                    'PermissionTitle' => 'Product Low & Out Stock',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view'],
                                    'translations' => [
                                        'en' => 'Product Low & Out Stock',
                                        'ar' => ' المنتجات منخفضة المخزون'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_BULK_IMPORT->value,
                                    'PermissionTitle' => 'Bulk Import',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view', 'insert', 'update'],
                                    'translations' => [
                                        'en' => 'Bulk Import',
                                        'ar' => 'الاستيراد بالجملة'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_BULK_EXPORT->value,
                                    'PermissionTitle' => 'Bulk Export',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view', 'insert', 'update'],
                                    'translations' => [
                                        'en' => 'Bulk Export',
                                        'ar' => 'التصدير بالجملة'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_PRODUCT_ATTRIBUTE_ADD->value,
                            'PermissionTitle' => 'Attributes',
                            'activity_scope' => 'store_level',
                            'icon' => 'Layers2',
                            'translations' => [
                                'en' => 'Attribute List',
                                'ar' => 'قائمة السمات'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_AUTHORS_MANAGE->value,
                            'PermissionTitle' => 'Authors',
                            'activity_scope' => 'store_level',
                            'icon' => 'BookOpenCheck',
                            'options' => ['View', 'insert', 'update', 'delete'],
                            'translations' => [
                                'en' => 'Author List',
                                'ar' => 'قائمة المؤلفين'
                            ]
                        ],
                    ]
                ],

                // Inventory Management
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Inventory Management',
                    'activity_scope' => 'store_level',
                    'icon' => 'SquareChartGantt',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Inventory Management',
                        'ar' => 'الإدارة المالية'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_PRODUCT_INVENTORY->value,
                            'PermissionTitle' => 'Inventory',
                            'activity_scope' => 'store_level',
                            'icon' => 'PackageOpen',
                            'options' => ['view'],
                            'translations' => [
                                'en' => 'Inventory',
                                'ar' => 'السحوبات'
                            ]
                        ]
                    ]
                ],

                // Promotional control
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Promotional control',
                    'activity_scope' => 'store_level',
                    'icon' => 'Proportions',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Promotional control',
                        'ar' => 'الرقابة الترويجية'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => '',
                            'PermissionTitle' => 'Flash Sale',
                            'activity_scope' => 'store_level',
                            'icon' => 'Zap',
                            'options' => ['View'],
                            'translations' => [
                                'en' => 'Flash Sale',
                                'ar' => 'بيع سريع'
                            ],
                            'submenu' => [
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PROMOTIONAL_FLASH_SALE_ACTIVE_DEALS->value,
                                    'PermissionTitle' => 'Active Deals',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view'],
                                    'translations' => [
                                        'en' => 'Active Deals',
                                        'ar' => 'عروض فلاش متاحة'
                                    ]
                                ],
                                [
                                    'PermissionName' => PermissionKey::SELLER_STORE_PROMOTIONAL_FLASH_SALE_MY_DEALS->value,
                                    'PermissionTitle' => 'My Deals',
                                    'activity_scope' => 'store_level',
                                    'icon' => '',
                                    'options' => ['view'],
                                    'translations' => [
                                        'en' => 'My Deals',
                                        'ar' => 'منتجاتي في العروض'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],

                // Support ticket  Management
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Support Ticket',
                    'activity_scope' => 'store_level',
                    'icon' => 'Headphones',
                    'options' => ['view', 'insert', 'update', 'delete', 'others'],
                    'translations' => [
                        'en' => 'Ticket',
                        'ar' => 'تذكرة الدعم'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_SUPPORT_TICKETS_MANAGE->value,
                            'PermissionTitle' => 'Tickets',
                            'activity_scope' => 'store_level',
                            'icon' => 'Headset',
                            'options' => ['view', 'insert', 'update', 'delete', 'others'],
                            'translations' => [
                                'en' => 'Support Ticket',
                                'ar' => 'السحوبات'
                            ]
                        ]
                    ]
                ],

                //Feedback control
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Feedback control',
                    'activity_scope' => 'store_level',
                    'icon' => 'MessageSquareReply',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Feedback control',
                        'ar' => 'التحكم في ردود الفعل'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_FEEDBACK_CONTROL_REVIEWS->value,
                            'PermissionTitle' => 'Reviews',
                            'activity_scope' => 'store_level',
                            'icon' => 'Star',
                            'translations' => [
                                'en' => 'Reviews',
                                'ar' => '/الدردشة'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_FEEDBACK_CONTROL_QUESTIONS->value,
                            'PermissionTitle' => 'Questions',
                            'activity_scope' => 'store_level',
                            'icon' => 'CircleHelp',
                            'translations' => [
                                'en' => 'Questions',
                                'ar' => 'الأسئلة/الدردشة'
                            ]
                        ]
                    ]
                ],


                // Financial Management
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Financial Management',
                    'activity_scope' => 'store_level',
                    'icon' => '',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Financial Management',
                        'ar' => 'الإدارة المالية'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_FINANCIAL_WITHDRAWALS->value,
                            'PermissionTitle' => 'Withdrawals',
                            'activity_scope' => 'store_level',
                            'icon' => 'BadgeDollarSign',
                            'options' => ['view', 'insert'],
                            'translations' => [
                                'en' => 'Withdrawals',
                                'ar' => 'السحوبات'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_FINANCIAL_WALLET->value,
                            'PermissionTitle' => 'Store Wallet',
                            'activity_scope' => 'store_level',
                            'icon' => 'Wallet',
                            'translations' => [
                                'en' => 'Store Wallet',
                                'ar' => 'محفظتي'
                            ]
                        ]
                    ]
                ],

                // Staff control
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Staff control',
                    'activity_scope' => 'store_level',
                    'icon' => 'UserRoundPen',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Staff control',
                        'ar' => 'التحكم بالمستخدم'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_STAFF_MANAGE->value,
                            'PermissionTitle' => 'Staff List',
                            'activity_scope' => 'store_level',
                            'icon' => 'Users',
                            'options' => ['view', 'insert', 'update', 'delete'],
                            'translations' => [
                                'en' => 'Staff List',
                                'ar' => 'قائمة'
                            ]
                        ],
//                        [
//                            'PermissionName' => PermissionKey::SELLER_STAFF_ROLES_STORE->value,
//                            'PermissionTitle' => 'Staff Roles',
//                            'activity_scope' => 'store_level',
//                            'icon' => 'LockKeyholeOpen',
//                            'translations' => [
//                                'en' => 'Staff Roles',
//                                'ar' => 'أدوار الموظفين'
//                            ]
//                        ],

                    ]
                ],

                // Message Settings
//                [
//                    'PermissionName' => '',
//                    'PermissionTitle' => 'Message',
//                    'activity_scope' => 'store_level',
//                    'icon' => 'MessageCircleMore',
//                    'options' => ['View'],
//                    'translations' => [
//                        'en' => 'Message',
//                        'ar' => 'إعدادات المتجر'
//                    ],
//                    'submenu' => [
//                        [
//                            'PermissionName' => PermissionKey::STORE_STORE_MESSAGE->value,
//                            'PermissionTitle' => 'Message',
//                            'activity_scope' => 'store_level',
//                            'icon' => '',
//                            'translations' => [
//                                'en' => 'Message',
//                                'ar' => 'رسالة'
//                            ]
//                        ]
//                    ]
//                ],

                // Notifications Settings
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Notifications',
                    'activity_scope' => 'store_level',
                    'icon' => 'MessageCircleMore',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Notifications',
                        'ar' => 'إعدادات المتجر'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_NOTIFICATION_MANAGEMENT->value,
                            'PermissionTitle' => 'All Notifications',
                            'activity_scope' => 'store_level',
                            'icon' => 'Bell',
                            'translations' => [
                                'en' => 'All Notifications',
                                'ar' => 'كل الإشعارات'
                            ]
                        ]
                    ]
                ],

                // Store Settings
                [
                    'PermissionName' => '',
                    'PermissionTitle' => 'Store Settings',
                    'activity_scope' => 'store_level',
                    'icon' => 'Store',
                    'options' => ['View'],
                    'translations' => [
                        'en' => 'Store Settings',
                        'ar' => 'إعدادات المتجر'
                    ],
                    'submenu' => [
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_BUSINESS_PLAN->value,
                            'PermissionTitle' => 'Business Plan',
                            'activity_scope' => 'store_level',
                            'icon' => 'BriefcaseBusiness',
                            'options' => ['view'],
                            'translations' => [
                                'en' => 'Business Plan',
                                'ar' => 'إشعار المتجر'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_STORE_NOTICE->value,
                            'PermissionTitle' => 'Notice',
                            'activity_scope' => 'store_level',
                            'icon' => 'BadgeAlert',
                            'options' => ['view'],
                            'translations' => [
                                'en' => 'Store Notice',
                                'ar' => 'إشعار المتجر'
                            ]
                        ],
                        [
                            'PermissionName' => PermissionKey::SELLER_STORE_MY_SHOP->value,
                            'PermissionTitle' => 'My Stores',
                            'activity_scope' => 'store_level',
                            'icon' => 'Store',
                            'options' => ['view', 'insert', 'update', 'delete'],
                            'translations' => [
                                'en' => 'My Stores',
                                'ar' => 'متاجري'
                            ]
                        ]
                    ]
                ]
            ]
        ];


        $page_list = array_merge($admin_main_menu, $shop_menu);

        foreach ($page_list as $x_mod) {
            foreach ($x_mod as $level_1) {

                $trans_level_1 = [];
                $options_l1 = isset($level_1['options']) && is_array($level_1['options']) ? $level_1['options'] : ['View'];

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
                        $options_l2 = isset($level_2['options']) && is_array($level_2['options']) ? $level_2['options'] : ['View'];

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
                                $options_l3 = isset($level_3['options']) && is_array($level_3['options']) ? $level_3['options'] : ['View'];

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
        $role = Role::where('id',2)->first();
        $role->givePermissionTo(Permission::whereIn('available_for',['store_level','COMMON'])->get());
        $user = User::whereEmail('owner@store.com')->first();
        // Assign default Store User to a Specific Role
        $user->assignRole($role);
    }
}
