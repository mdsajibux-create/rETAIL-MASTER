<?php

namespace Modules\Chat\app\Traits;


use App\Enums\PermissionKey;
use Modules\Chat\app\Models\Chat;
use Modules\Chat\app\Models\ChatMessage;

trait ChatSeederMenu
{
    public function chatMenus(): array
    {
        return [
            [
                'PermissionName' => '',
                'PermissionTitle' => 'Chat Management',
                'activity_scope' => 'system_level',
                'icon' => '',
                'options' => ['view'],
                'translations' => [
                    'en' => 'Chat Management',
                    'ar' => 'إدارة الدردشة'
                ],
                'submenu' => [
                    [
                        'PermissionName' => '',
                        'PermissionTitle' => 'Chat',
                        'activity_scope' => 'system_level',
                        'icon' => 'MessageSquareMore',
                        'options' => ['view', 'update', 'delete'],
                        'translations' => [
                            'en' => 'Chat',
                            'ar' => 'إعدادات الدردشة'
                        ],

                        'submenu' => [
                            [
                                'PermissionName' => PermissionKey::ADMIN_CHAT_SETTINGS->value,
                                'PermissionTitle' => 'Chat Settings',
                                'activity_scope' => 'system_level',
                                'icon' => '',
                                'options' => ['view', 'update'],
                                'translations' => [
                                    'en' => 'Chat Settings',
                                    'ar' => 'إعدادات الدردشة'
                                ]
                            ],
                            [
                                'PermissionName' => PermissionKey::ADMIN_CHAT_MANAGE->value,
                                'PermissionTitle' => 'Chat List',
                                'activity_scope' => 'system_level',
                                'icon' => '',
                                'options' => ['view', 'update'],
                                'translations' => [
                                    'en' => 'Chat List',
                                    'ar' => 'قائمة الدردشة'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
