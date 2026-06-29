<?php

use App\Enums\Role;

function getPermissionMiddleware(string $permission = ''): string
{
    return ($permission && array_key_exists($permission, config('middleware')))
        ? 'permission:' . implode('|', config('middleware.' . $permission))
        : 'role:' . Role::SUPER_ADMIN->value;
}