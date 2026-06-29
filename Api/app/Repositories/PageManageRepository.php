<?php

namespace App\Repositories;

use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\PageDetailsResource;
use App\Http\Resources\PageResource;
use App\Interfaces\PageManageInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\SystemCore\app\Models\Page;

class PageManageRepository implements PageManageInterface
{
    public function __construct(protected Page $page, protected Translation $translation)
    {
    }

    public function translationKeysForPage(): mixed
    {
        return $this->page->translationKeys;
    }

    public function getPaginatedPage(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $query = Page::query()->with('related_translations');
        $paginatedPage = $query->orderBy($sortField, $sort)->paginate($limit);

        return response()->json([
            'data' => PageResource::collection($paginatedPage),
            'meta' => new PaginationResource($paginatedPage),
        ]);
    }

    public function getPageById(int|string $id)
    {

        try {

            $page = Page::with('related_translations')->where('id', (int)$id)->first();

            if (!$page) {
                return response()->json([
                    "message" => __('messages.data_not_found')
                ], 404);
            }

            if ($page->enable_builder === 1){
                $value = $page->content;
                $content = is_array($value) ? jsonImageModifierFormatter($value) : [];
                $page->content = $content;

                return response()->json([
                    'data' => new PageDetailsResource($page),
                ]);
            }

            return response()->json([
                'data' => new PageDetailsResource($page),
            ]);

        } catch (\Throwable $th) {
            return response()->json([
            ], 500);
        }
    }

    public function store(array $data, string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("The provided model class does not exist: $modelClass");
        }
        try {
            $data = Arr::except($data, ['translations']);
            $final = $modelClass::create($data);
            return $final->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data, string $modelClass)
    {
        if (!class_exists($modelClass)) {
            throw new \InvalidArgumentException("The provided model class does not exist: $modelClass");
        }
        try {
            $final = $modelClass::findOrFail($data['id']);
            if ($final) {
                $data = Arr::except($data, ['translations']);
                $final->update($data);
                return $final->id;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id, string $modelClass)
    {
        try {
            $final = $modelClass::findOrFail($id);
            $this->deleteTranslation($final->id, $modelClass);
            $final->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function deleteTranslation(int|string $id, string $translatable_type)
    {
        try {
            Translation::where('translatable_id', $id)
                ->where('translatable_type', $translatable_type)
                ->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }
                    // Collect translation data
                    $translations[] = [
                        'translatable_type' => $refPath,
                        'translatable_id' => $refid,
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }

                    $trans = $this->translation->where('translatable_type', $refPath)->where('translatable_id', $refid)
                        ->where('language', $translation['language_code'])->where('key', $key)->first();
                    if ($trans != null) {
                        $trans->value = $translatedValue;
                        $trans->save();
                    } else {
                        $translations[] = [
                            'translatable_type' => $refPath,
                            'translatable_id' => $refid,
                            'language' => $translation['language_code'],
                            'key' => $key,
                            'value' => $translatedValue,
                        ];
                    }
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

}
