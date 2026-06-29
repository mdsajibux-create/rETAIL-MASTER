<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\MenuPublicDetailsResource;
use App\Http\Resources\MenuPublicViewResource;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\SystemCore\app\Models\Menu;

class  MenuManageController extends Controller
{
    public function __construct(protected Menu $menu, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->menu->translationKeys;
    }

    public function menus(Request $request)
    {
        $per_page = $request->per_page ?? 10;
        $language = $request->language ?? config('app.default_language');;
        $search = $request->search;
        $isPaginationDisabled = $request->has('pagination') && $request->pagination === "false";
        $menus = Menu::leftJoin('translations', function ($join) use ($language) {
            $join->on('menus.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', Menu::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })->select(
            'menus.*',
            DB::raw('COALESCE(translations.value, menus.name) as name')
        );

        // Apply search filter if search parameter exists
        if ($search) {
            $menus->where(function ($query) use ($search) {
                $query->where('translations.value', 'like', "%{$search}%")
                    ->orWhere('menus.name', 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        $sortField = $request->sortField ?? 'position';
        $sortOrder = $request->sort ?? 'asc';

        if ($isPaginationDisabled) {
            $menus = $menus
                ->where('is_visible', true)
                ->with('childrenRecursive')
                ->whereNull('parent_id')
                ->orderBy($sortField, $sortOrder)
                ->get();
            return response()->json([
                'menus' => MenuPublicViewResource::collection($menus),
            ]);
        } else {
            $menus = $menus
                ->orderBy($sortField, $sortOrder)
                ->paginate($per_page);
            return response()->json([
                'menus' => MenuPublicViewResource::collection($menus),
                'meta' => new PaginationResource($menus)
            ]);
        }
    }


    // Create a new menu item
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'nullable|string',
            'icon' => 'nullable|string',
            'position' => 'nullable|integer',
            'is_visible' => 'boolean',
            'parent_id' => 'nullable|exists:menus,id',
            'parent_path' => 'nullable|string',
            'menu_path' => 'nullable|string',
            'menu_content' => 'nullable|json',
            'translations' => 'nullable|array',
            'translations.*.language' => 'nullable|string',
            'translations.*.value' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $menu = Menu::create([
                'page_id' => $request->page_id,
                'name' => $request->name,
                'url' => $request->url,
                'icon' => $request->icon,
                'position' => $request->position ?? 0,
                'is_visible' => $request->is_visible ?? true,
                'parent_id' => $request->parent_id,
                'parent_path' => $request->parent_path,
                'menu_path' => $request->menu_path,
                'menu_level' => $request->parent_id ? Menu::find($request->parent_id)->menu_level + 1 : 0,
                'menu_content' => $request->menu_content,
            ]);

            // Save translations
            if ($request->has('translations')) {
                createOrUpdateTranslation($request, $menu->id, 'Modules\SystemCore\app\Models\Menu', $this->translationKeys());
                createOrUpdateTranslationJson($request, $menu->id, 'Modules\SystemCore\app\Models\Menu', ['menu_content']);
            }

            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Menu']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        $menu = Menu::with('childrenRecursive', 'related_translations')->find($request->id);

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }
        return response()->json([
            'data' => new MenuPublicDetailsResource($menu),
        ]);
    }


    // Update an existing menu item
    public function update(Request $request)
    {
        $menu = Menu::find($request->id);
        // Prevent updating URL if status is 0
        if ($menu->status == 0 && $request->has('url') && $request->url !== $menu->url) {
            return response()->json([
                'message' => __('messages.can\'t_modify', ['name' => 'Menu url'])
            ], 403);
        }

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'url' => 'nullable|string',
            'icon' => 'nullable|string',
            'position' => 'nullable|integer',
            'is_visible' => 'boolean',
            'parent_id' => 'nullable|exists:menus,id|not_in:' . $request->id,
            'parent_path' => 'nullable|string',
            'menu_path' => 'nullable|string',
            'menu_content' => 'nullable|json',
            'translations' => 'nullable|array',
            'translations.*.language' => 'nullable|string',
            'translations.*.value' => 'nullable|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()
            ], 422);
        }

        try {
            $menu->update([
                'page_id' => $request->page_id,
                'name' => $request->name,
                'url' => $menu->status == 0 ? $menu->url : $request->url,
                'icon' => $request->icon,
                'position' => $request->position ?? 0,
                'is_visible' => $request->is_visible ?? true,
                'parent_id' => $request->parent_id,
                'parent_path' => $request->parent_path,
                'menu_path' => $request->menu_path,
                'menu_level' => $request->parent_id ? Menu::find($request->parent_id)->menu_level + 1 : 0,
                'menu_content' => $request->menu_content,
            ]);
            if ($request->has('translations')) {
                createOrUpdateTranslation($request, $menu->id, 'Modules\SystemCore\app\Models\Menu', $this->translationKeys());
                createOrUpdateTranslationJson($request, $menu->id, 'Modules\SystemCore\app\Models\Menu', ['menu_content']);
            }

            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Menu']),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }

    }

    public function updatePosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:menus,id',
            'position' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $menu = Menu::find($request->id);
        if (!$menu) {
            return response()->json([
                'message' => __('messages . data_not_found')
            ], 404);
        }
        $menu->update([
            'position' => $request->position,
        ]);
        return response()->json([
            'message' => __('messages . update_success', ['name' => 'Menu']),
        ]);
    }

    // Delete a menu item
    public function destroy($id)
    {
        $menu = Menu::find($id);
        if ($menu->status == 0) {
            return response()->json(
                ['message' => __('messages . can\'t_modify', ['name' => 'Menu'])]
            );
        }
        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        // Check if has children
        if ($menu->children()->count() > 0) {
            return response()->json(['message' => 'Cannot delete a menu that has child menus'], 400);
        }

        $menu->related_translations()->delete();
        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully']);
    }
}
