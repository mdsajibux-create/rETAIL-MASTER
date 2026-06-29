<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CustomPermission extends Permission
{
    /**
     * Define a polymorphic relationship for translations.
     */
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Define a relationship to fetch child permissions.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Define a recursive relationship to fetch all descendants.
     */
    public function childrenRecursive()
    {
        return $this->hasMany(CustomPermission::class, 'parent_id')->with('childrenRecursive');
    }

    /**
     * Define a relationship to fetch the parent permission.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function roles(): belongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
