<?php

namespace App\Models;

use Spatie\Permission\Models\Role;

class CustomRole extends Role
{

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CustomPermission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    public function childrenRecursive()
    {
        return $this->hasMany(CustomPermission::class, 'parent_id')->with('permissions', 'childrenRecursive'); // Ensure 'permissions' is loaded
    }
}
