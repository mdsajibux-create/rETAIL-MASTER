<?php

namespace App\Repositories;

use App\Interfaces\DynamicFieldInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\DynamicField;
use Modules\Catalog\app\Models\DynamicFieldValue;

class DynamicFieldRepository implements DynamicFieldInterface
{
    public function __construct(protected DynamicField $dynamic_field, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->dynamic_field->translationKeys;
    }

    public function model(): string
    {
        return DynamicField::class;
    }

    public function getPaginatedDynamicField(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $dynamic_field = DynamicField::leftJoin('translations', function ($join) use ($language) {
            $join->on('dynamic_fields.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', DynamicField::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })
            ->select(
                'dynamic_fields.*',
                DB::raw('COALESCE(translations.value, dynamic_fields.name) as name')
            );


        // Apply search filter if search parameter exists
        if ($search) {
            $dynamic_field->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', dynamic_fields.name, translations.value)"), 'like', "%{$search}%");
            });
        }

        $dynamic_fields = $dynamic_field->with('related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);

        return $dynamic_fields;
    }

    public function store(array $data)
    {
        try {
            $specData = Arr::except($data, ['translations']);
            $dynamic_field = DynamicField::create([
                'name'        => $specData['name'],
                'slug'        => $specData['slug'],
                'product_type'  => $specData['product_type'],
                'type'        => $specData['type'],
                'is_required' => $specData['is_required'] ?? false,
            ]);

            return $dynamic_field->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        try {
            $dynamic_field = DynamicField::findOrFail($data['id']);
            $specData = Arr::except($data, ['translations']);

            // Update main specification fields
            $dynamic_field->update([
                'name'        => $specData['name'] ?? $dynamic_field->name,
                'slug'        => $specData['slug'] ?? $dynamic_field->slug,
                'store_type'  => $specData['store_type'] ?? $dynamic_field->store_type,
                'type'        => $specData['type'] ?? $dynamic_field->type,
                'is_required' => $specData['is_required'] ?? $dynamic_field->is_required,
                'status' => $specData['status'] ?? $dynamic_field->status,
            ]);

            return $dynamic_field->id;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id)
    {
        try {
            $dynamic_field = DynamicField::findOrFail($id);
            if ($dynamic_field){
                $this->deleteTranslation($dynamic_field->id, DynamicField::class);
                $dynamic_field->delete();
            }


            // delete related option value
            $dynamic_field_value = DynamicFieldValue::where('dynamic_field_id', $dynamic_field->id)->first();
            if ($dynamic_field_value){
                $dynamic_field_value->delete();
                $this->deleteTranslation($dynamic_field_value->id, DynamicFieldValue::class);
            }

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

    public function getDynamicOptionForProduct(string $product_type)
    {
        $dynamic_field = DynamicField::with('related_translations', 'values.translations')
            ->where('product_type', $product_type)
            ->get();

        // if not empty and not 0
        if (!empty($dynamic_field) && $dynamic_field->count() > 0) {
            return $dynamic_field;
        } else {
            return false;
        }

    }

    public function getDynamicFieldById(int|string $id)
    {
        $dynamic_field = DynamicField::with('related_translations')->find($id);
        if ($dynamic_field) {
            return $dynamic_field;
        } else {
            return false;
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

                    $trans = $this->translation
                        ->where('translatable_type', $refPath)
                        ->where('translatable_id', $refid)
                        ->where('language', $translation['language_code'])
                        ->where('key', $key)
                        ->first();

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
