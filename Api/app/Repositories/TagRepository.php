<?php

namespace App\Repositories;

use App\Http\Resources\Admin\AdminTagDetailsResource;
use App\Http\Resources\Admin\AdminTagResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\TagInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\Tag;

class TagRepository implements TagInterface
{
    public function __construct(protected Tag $tag, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->tag->translationKeys;
    }

    public function model(): string
    {
        return Tag::class;
    }

    public function getPaginatedTag(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $tag = Tag::leftJoin('translations', function ($join) use ($language) {
            $join->on('tags.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', Tag::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })
            ->select(
                'tags.*',
                DB::raw('COALESCE(translations.value, tags.name) as name')
            );
        // Apply search filter if search parameter exists
        if ($search) {
            $tag->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', tags.name, translations.value)"), 'like', "%{$search}%");
            });
        }
        // Apply sorting and pagination
        // Return the result
        $tags = $tag->with('related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);
        return response()->json([
            'data' => AdminTagResource::collection($tags),
            'meta' => new PaginationResource($tags)
        ]);
    }

    public function store(array $data)
    {
        try {
            $data = Arr::except($data, ['translations']);
            $tag = Tag::create($data);

            return $tag->id;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTagById(int|string $id)
    {
        try {
            $tag = Tag::with('related_translations')->find($id);
            if ($tag) {
                return response()->json([
                    "data" => new AdminTagDetailsResource($tag),
                ]);
            } else {
                return response()->json([
                    "massage" => __('message.data_not_found'),
                ], 404);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        try {
            $tag = Tag::findOrFail($data['id']);
            if ($tag) {
                $data = Arr::except($data, ['translations']);
                $tag->update($data);
                return $tag->id;
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id)
    {
        try {
            $tag = Tag::findOrFail($id);
            $this->deleteTranslation($tag->id, Tag::class);
            $tag->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function deleteTranslation(int|string $id, string $translatable_type)
    {
        try {
            $translation = Translation::where('translatable_id', $id)
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
