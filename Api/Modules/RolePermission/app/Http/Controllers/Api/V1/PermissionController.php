<?php

namespace Modules\RolePermission\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Enums\PermissionKey;
use App\Helpers\ComHelper;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\UserRoleResource;
use App\Models\CustomPermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class PermissionController extends Controller
{

    public function moduleWisePermissions(Request $request)
    {
        $permissions = QueryBuilder::for(CustomPermission::class)
            ->when($request->filled('available_for'), function (Builder $query) use ($request) {
                $query->where('available_for', $request->available_for);
            })
            ->whereNull('parent_id') // Start with top-level permissions
            ->with('childrenRecursive') // Include recursive children
            ->get();

        return ComHelper::buildMenuTree([0], $permissions);
    }

    public function permissions(Request $request)
    {

        $user = Auth::guard('sanctum')->user();
        $roleIds = $user->roles()->pluck('id');
        $brand_count = 1;

        $permissions = null;

            if ($user->activity_scope == 'system_level') {
                $permissions = $user->rolePermissionsQuery() // Start from permissions assigned to the user's roles
                ->whereNull('parent_id') // Fetch only top-level (root) permissions (i.e., no parent)
                ->with([
                    // Eager load children recursively, but only if those children are assigned to the user's roles
                    'childrenRecursive' => function ($query) use ($roleIds) {
                        $query->whereHas('roles', function ($q) use ($roleIds) {
                            // Filter to include only permissions that are assigned to the user's roles
                            $q->whereIn('role_id', $roleIds);
                        })->with([
                            // Now for each child, load their children (grandchildren) recursively,
                            // again only if assigned to the user's roles
                            'childrenRecursive' => function ($subQuery) use ($roleIds) {
                                $subQuery->whereHas('roles', function ($q) use ($roleIds) {
                                    $q->whereIn('role_id', $roleIds);
                                });
                            }]);
                    }
                ])->get(); // Finally, execute the query and get the results

            } else {

                // get staff all permission
                $staff_all_permissions = $user->rolePermissionsQuery()->get();
                $permission_lists = [];

                // Only collect the direct permissions (no children)
                foreach ($staff_all_permissions as $permission) {
                    if (!empty($permission->name)) {
                        $permission_lists[] = $permission->name;
                    }
                }
                // Remove duplicates and empty values
                $permissionsArray = array_values(array_unique(array_filter($permission_lists)));


            // Get only the permissions listed
            $permissions = $user->rolePermissionsQuery()
                ->whereIn('name', $permissionsArray)
                ->get()
                ->map(function ($permission) {
                    // Decode options if it's a string
                    if (is_string($permission->options)) {
                        $permission->options = json_decode($permission->options, true);
                    }

                    // Add type info
                    $permission->type = $permission->parent_id === null ? 'parent' : 'child';

                    return $permission;
                });

            // Identify parent and child
            $parents = $permissions->filter(fn($p) => $p->parent_id === null)->keyBy('id');
            $children = $permissions->filter(fn($p) => $p->parent_id !== null);

            // Attach children only if their parent exists in the list
            foreach ($children as $child) {
                if ($parents->has($child->parent_id)) {
                    $child->type = 'child';
                    $parents[$child->parent_id]->children[] = $child;
                }
            }

            // Result: parent with children (if both in array), and also track type
            $permissions->map(function ($permission) use ($parents) {
                $permission->type = $permission->parent_id === null ? 'parent' : 'child';
                return $permission;
            });
        }

        $filteredPermissions = ComHelper::buildMenuTree($user->roles()->pluck('id')->toArray(), $permissions);

        function filterTrueOnly(array $permissions): array
        {
            $result = [];

            foreach ($permissions as $permission) {
                // Normalize keys to avoid undefined index notices
                $options  = $permission['options'] ?? [];
                $children = $permission['children'] ?? [];

                // Keep only options that are strictly true
                $filteredOptions = array_values(array_filter($options, function ($opt) {
                    return isset($opt['value']) && $opt['value'] === true;
                }));

                // Recurse children
                $filteredChildren = !empty($children)
                    ? filterTrueOnly($children)
                    : [];

                $hadOptionsOriginally = !empty($options);

                // Keep node only if it has true options or remaining children
                if ($hadOptionsOriginally || !empty($filteredChildren)) {
                    $permission['options']  = $filteredOptions; // may be empty
                    $permission['children'] = $filteredChildren;
                    $result[] = $permission;
                }
            }

            return $result;
        }

        // Apply filter
        $final_filteredPermissions = filterTrueOnly($filteredPermissions);


        // SMART FIX: Remove child permissions that appear at root level
        function removeChildDuplicates($permissions) {
            // Collect all child IDs recursively
            $childIds = [];
            foreach ($permissions as $permission) {
                if (!empty($permission['children'])) {
                    collectChildIds($permission['children'], $childIds);
                }
            }

            // Remove root level items that are children of other items
            return array_values(array_filter($permissions, function($permission) use ($childIds) {
                return !in_array($permission['id'], $childIds);
            }));
        }

        function collectChildIds($children, &$childIds) {
            foreach ($children as $child) {
                $childIds[] = $child['id'];
                if (!empty($child['children'])) {
                    collectChildIds($child['children'], $childIds);
                }
            }
        }

        // Apply the duplicate removal
        $final_filteredPermissions = removeChildDuplicates($final_filteredPermissions);

        // Apply system-level restriction
        if ($user->activity_scope === 'system_level'){
            // Fetch allowed permission IDs
            $user_permission_ids = Permission::where('available_for', 'system_level')
                ->pluck('id')
                ->toArray();

            $filtered = filterPermissionsByIds(
                $final_filteredPermissions,
                $user_permission_ids
            );

            //  FALLBACK: if nothing matched, return default permissions
            if (!empty($filtered)) {
                $final_filteredPermissions = $filtered;
            }
        };

        return [
            "permissions" => $final_filteredPermissions,
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'email' => $user->email,
            'activity_scope' => $user->activity_scope,
        ];
    }

    // permission false remove in tree
    private function propagateViewTrue(&$permissions)
    {
        foreach ($permissions as &$permission) {
            if (!empty($permission['children'])) {
                // Recursively process children
                $this->propagateViewTrue($permission['children']);

                // If any child has view=true, mark parent as true
                foreach ($permission['children'] as $child) {
                    foreach ($child['options'] as $opt) {
                        if (strtolower($opt['label']) === 'view' && $opt['value'] === true) {
                            // Check if parent already has a view option
                            $parentHasView = false;
                            foreach ($permission['options'] as &$parentOpt) {
                                if (strtolower($parentOpt['label']) === 'view') {
                                    $parentOpt['value'] = true;
                                    $parentHasView = true;
                                    break;
                                }
                            }

                            // If parent doesn't have view option, add it
                            if (!$parentHasView) {
                                $permission['options'][] = [
                                    'label' => 'view',
                                    'value' => true
                                ];
                            }
                            break;
                        }
                    }
                }
            }
        }
    }

    // filter permission
    private function filterPermissionsWithAccess($permissions) {
        $filtered = [];

        foreach ($permissions as $permission) {
            // Filter options to only include those with value = true
            $filteredOptions = array_filter($permission['options'], function($option) {
                return $option['value'] === true;
            });

            // Recursively filter children
            $filteredChildren = [];
            if (!empty($permission['children'])) {
                $filteredChildren = $this->filterPermissionsWithAccess($permission['children']);
            }

            // Include this permission if:
            // 1. It has at least one option with value = true, OR
            // 2. It has children with access (for parent permissions)
            if (!empty($filteredOptions) || !empty($filteredChildren)) {
                $permission['options'] = array_values($filteredOptions); // Re-index array
                $permission['children'] = $filteredChildren;
                $filtered[] = $permission;
            }
        }

        return $filtered;
    }

    public function roles(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $roles = collect();
        if ($user->activity_scope === 'branch_level') {
            $roles = Role::where('available_for', 'branch_level')
                ->where('status', 1)
                ->get();
        }

        return [
            'id' => $user->id,
            'activity_scope' => $user->activity_scope,
            'roles' => UserRoleResource::collection($roles),
        ];
    }

    public function index(Request $request)
    {

        $limit = $request->limit ?? 10;
        $permissions = QueryBuilder::for(PermissionKey::class)
            ->when($request->filled('available_for'), function ($query) use ($request) {
                $query->where('available_for', $request->available_for);
            })
            ->paginate($limit);
        return PermissionResource::collection($permissions);
    }


    public function permissionForStoreOwner(Request $request)
    {
        $permission = PermissionKey::findOrFail($request->id);
        $permission->available_for = $permission->available_for === 'system_level' ? 'branch_level' : 'system_level';
        $permission->save();
        return response()->json([
            'success' => true,
            'message' => 'PermissionKey for Branch Admin toggled successfully',
            'status' => $permission
        ]);
    }
}
