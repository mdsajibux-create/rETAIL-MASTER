<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Http\Resources\Com\PaginationResource;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ManageLanguageController
{

    public function index()
    {
        $languages = Language::orderBy('id', 'asc')->get();

        $languages->transform(function ($language) {
            $language->translations = json_decode($language->translations, true); // decode as array
            return $language;
        });

        return response()->json([
            'languages' => $languages,
        ]);
    }
    public function list(Request $request)
    {
        $per_page  = $request->per_page ?? 10;
        $sortOrder = $request->sort ?? 'asc'; // asc | desc

        // Validate sort direction
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }

        $languages = Language::orderBy('id', $sortOrder)
            ->paginate($per_page);

        // Decode translations JSON
        $languages->getCollection()->transform(function ($language) {
            $language->translations = json_decode($language->translations, true);
            return $language;
        });

        return response()->json([
            'success' => true,
            'languages' => $languages->items(),
            'meta' => new PaginationResource($languages)
        ]);
    }

    public function addLanguage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('languages', 'name')->ignore($request->id),
            ],
            'translations' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {

            if (!$request->translations) {
                $defaultLanguage = Language::where('is_default', true)->first();
                if ($defaultLanguage) {
                    $translations = json_decode($defaultLanguage->translations, true);
                } else {

                    $translations = [];
                }
            }

            $language = Language::updateOrCreate(
                ['code' => $request->code], // search condition
                [
                    'name' => $request->name,
                    'translations' => $request->translations ? json_encode($request->translations, JSON_UNESCAPED_UNICODE): json_encode($translations, JSON_UNESCAPED_UNICODE),
                    'status' => $request->status,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Language saved successfully',
                'data' => $language
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
            ], 500);
        }
    }


    public function showLanguage(Request $request, $id)
    {
        $validator = Validator::make(
            ['id' => $id],
            ['id' => 'required|integer|exists:languages,id']
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $languages = Language::find($id);

        return response()->json([
            'success' => true,
            'data'    => $languages,
            'message' => 'Language retrieved successfully'
        ]);
    }

    public function updateLanguage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:languages,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('languages', 'name')->ignore($request->id),
            ],
            'translations' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
            ], 422);
        }

        try {

            $language = Language::findOrFail($request->id);

            $language->update([
                'name' => $request->name,
                'translations' => json_encode($request->translations, JSON_UNESCAPED_UNICODE),
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Language updated successfully',
                'data' => $language
            ]);

        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
            ], 500);
        }
    }


    public function deleteLanguage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
            ]);
        }

        $delete = Language::findOrFail($request->id);

        // if set default not delete
        if ($delete->is_default){
            return response()->json([
                'success' => true,
                'message' => 'Default Language not deleted',
            ]);
        }

        $delete->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:languages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $language = Language::find($request->id);

        if (!$language) {
            return response()->json([
                'success' => false,
                'message' => 'Language not found'
            ], 404);
        }

        // If already default, do nothing
        if ($language->is_default) {
            return response()->json([
                'success' => true,
                'message' => 'This language is already the default'
            ]);
        }

        // Remove default from others
        Language::where('is_default', true)->update(['is_default' => false]);

        // Set selected language as default
        $language->is_default = true;
        $language->save();

        return response()->json([
            'success' => true,
            'message' => 'Default language updated successfully',
            'data' => $language
        ], 200);
    }


    public function searchKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|integer|exists:languages,id',
            'keyword'     => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $language     = Language::findOrFail($request->language_id);
        $translations = json_decode($language->translations, true) ?? [];

        if ($request->filled('keyword')) {
            $keyword      = strtolower($request->keyword);
            $translations = array_filter($translations, function ($value, $key) use ($keyword) {
                return str_contains(strtolower($key), $keyword)
                    || str_contains(strtolower($value), $keyword);
            }, ARRAY_FILTER_USE_BOTH);
        }

        return response()->json([
            'success' => true,
            'data'    => $translations,
            'total'   => count($translations),
        ]);
    }

    public function addKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|integer|exists:languages,id',
            'key'         => 'required|string|max:255',
            'value'       => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $language     = Language::findOrFail($request->language_id);
            $translations = json_decode($language->translations, true) ?? [];

            if (array_key_exists($request->key, $translations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key already exists. Use update instead.',
                ], 422);
            }

            $translations[$request->key] = $request->value;

            $language->update([
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Key added successfully',
                'data'    => ['key' => $request->key, 'value' => $request->value],
            ]);

        } catch (\Exception $exception) {
            return response()->json(['success' => false], 500);
        }
    }

    public function updateKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|integer|exists:languages,id',
            'old_key'     => 'required|string|max:255',
            'new_key'     => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $language     = Language::findOrFail($request->language_id);
            $translations = json_decode($language->translations, true) ?? [];

            if (!array_key_exists($request->old_key, $translations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key not found',
                ], 404);
            }

            if (array_key_exists($request->new_key, $translations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'New key already exists',
                ], 422);
            }

            // Rename: keep value, swap key
            $translations[$request->new_key] = $translations[$request->old_key];
            unset($translations[$request->old_key]);

            $language->update([
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Key renamed successfully',
                'data'    => ['old_key' => $request->old_key, 'new_key' => $request->new_key],
            ]);

        } catch (\Exception $exception) {
            return response()->json(['success' => false], 500);
        }
    }

    public function removeKey(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => 'required|integer|exists:languages,id',
            'key'         => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $language     = Language::findOrFail($request->language_id);
            $translations = json_decode($language->translations, true) ?? [];

            if (!array_key_exists($request->key, $translations)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key not found',
                ], 404);
            }

            unset($translations[$request->key]);

            $language->update([
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Key removed successfully',
            ]);

        } catch (\Exception $exception) {
            return response()->json(['success' => false], 500);
        }
    }



}
