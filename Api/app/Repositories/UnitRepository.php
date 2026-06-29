<?php

namespace App\Repositories;

use App\Interfaces\UnitInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\Unit;

class UnitRepository implements UnitInterface
{
    public function __construct(protected Unit $unit, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->unit->translationKeys;
    }

    public function model(): string
    {
        return Unit::class;
    }

    public function getPaginatedUnit(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $unit = Unit::leftJoin('translations', function ($join) use ($language) {
            $join->on('units.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', Unit::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })
            ->select(
                'units.*',
                DB::raw('COALESCE(translations.value, units.name) as name')
            );


        // Apply search filter if search parameter exists
        if ($search) {
            $unit->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', units.name, translations.value)"), 'like', "%{$search}%");
            });
        }
        // Apply sorting and pagination
        // Return the result
        $units = $unit->with('related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);
        return $units;
    }

    public function store(array $data)
    {
        try {
            $data = Arr::except($data, ['translations']);
            $unit = Unit::create($data);
            return $unit->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        try {
            $unit = Unit::findOrFail($data['id']);
            if ($unit) {
                $data = Arr::except($data, ['translations']);
                $unit->update($data);
                return $unit->id;
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
            $unit = Unit::findOrFail($id);
            $this->deleteTranslation($unit->id, Unit::class);
            $unit->delete();
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

    public function getUnitById(int|string $id)
    {
        try {
            $unit = Unit::with('related_translations')->findOrFail($id);
            if ($unit) {
                return $unit;
            } else {
                return response()->json([
                    "massage" => "Data was not found"
                ], 404);
            }
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
