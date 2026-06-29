<?php

namespace App\Repositories;

use App\Interfaces\DynamicFieldOptionInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\DynamicField;
use Modules\Catalog\app\Models\DynamicFieldValue;

class DynamicFieldOptionRepository implements DynamicFieldOptionInterface
{
    public function __construct(protected DynamicFieldValue $dynamic_field_value, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->dynamic_field_value->translationKeys;
    }

    public function model(): string
    {
        return DynamicFieldValue::class;
    }

    public function getPaginatedDynamicField(
        int|string $dynamic_field_id,
        int|string $limit,
        int $page,
        string $language,
        string $search,
        string $sortField,
        string $sort,
        array $filters)
    {
        $dynamic_field = DynamicFieldValue::where('dynamic_field_id', $dynamic_field_id)
            ->leftJoin('translations', function ($join) use ($language) {
            $join->on('dynamic_field_values.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', DynamicFieldValue::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'value');
        })->select(
                'dynamic_field_values.*',
                DB::raw('COALESCE(translations.value, dynamic_field_values.value) as value')
            );


        // Apply search filter if search parameter exists
        if ($search) {
            $dynamic_field->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', dynamic_field_values.value, translations.value)"), 'like', "%{$search}%");
            });
        }

        $dynamic_fields = $dynamic_field->with('related_translations','dynamicField.related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);

        return $dynamic_fields;
    }

    public function store(array $data)
    {
        try {
            $dynamic_field = DynamicField::find($data['dynamic_field_id']);
            $value = $data['value'];

            // create
            if (in_array($dynamic_field->type, ['select', 'multiselect', 'checkbox', 'text'])) {
                $dynamic_field_value =  DynamicFieldValue::create([
                    'dynamic_field_id' => $dynamic_field->id,
                    'value' => $value,
                ]);
                return $dynamic_field_value->id;
            }

            return false;

        } catch (\Throwable $th) {
            throw $th;
        }

    }

    public function update(array $data)
    {

        try {
            $dynamic_field = DynamicField::find($data['dynamic_field_id']);
            $value = $data['value'];

            // update
            $dynamic_field_value = DynamicFieldValue::where('id', $data['id'])
                ->where('dynamic_field_id', $dynamic_field->id)
                ->first();

            $dynamic_field_value->value = $value;
            $dynamic_field_value->save();

            return $dynamic_field_value->id;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete(int|string $id)
    {
        try {
            $dynamic_field = DynamicFieldValue::findOrFail($id);
            $this->deleteTranslation($dynamic_field->id, DynamicFieldValue::class);
            $dynamic_field->delete();
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

    public function getDynamicFieldById(int|string $id)
    {
        $dynamic_field = DynamicFieldValue::with('related_translations')->find($id);

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
