<?php

namespace App\Repositories;

use App\Http\Resources\Admin\AdminSliderDetailsResource;
use App\Interfaces\AllSliderManageInterface;
use App\Models\Slider;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AllSliderManageRepository implements AllSliderManageInterface
{
    public function __construct(protected Slider $slider, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->slider->translationKeys;
    }

    public function getPaginatedSlider(int|string $limit, string $language, string $search, string $sortField, string $sort, string $platform)
    {
        $slider = Slider::leftJoin('translations as title_translations', function ($join) use ($language) {
            $join->on('sliders.id', '=', 'title_translations.translatable_id')
                ->where('title_translations.translatable_type', '=', Slider::class)
                ->where('title_translations.language', '=', $language)
                ->where('title_translations.key', '=', 'title');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('sliders.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Slider::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->leftJoin('translations as sub_title_translations', function ($join) use ($language) {
                $join->on('sliders.id', '=', 'sub_title_translations.translatable_id')
                    ->where('sub_title_translations.translatable_type', '=', Slider::class)
                    ->where('sub_title_translations.language', '=', $language)
                    ->where('sub_title_translations.key', '=', 'sub_title');
            })
            ->leftJoin('translations as button_text_translations', function ($join) use ($language) {
                $join->on('sliders.id', '=', 'button_text_translations.translatable_id')
                    ->where('button_text_translations.translatable_type', '=', Slider::class)
                    ->where('button_text_translations.language', '=', $language)
                    ->where('button_text_translations.key', '=', 'button_text');
            })
            ->select(
                'sliders.*',
                DB::raw('COALESCE(title_translations.value, sliders.title) as title'),
                DB::raw('COALESCE(description_translations.value, sliders.description) as description'),
                DB::raw('COALESCE(sub_title_translations.value, sliders.sub_title) as sub_title'),
                DB::raw('COALESCE(button_text_translations.value, sliders.button_text) as button_text')
            );
        // Apply search filter if search parameter exists
        if (!empty($search)) {
            $slider->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', 
                sliders.title, title_translations.value, 
                sliders.description, description_translations.value, 
                sliders.sub_title, sub_title_translations.value, 
                sliders.button_text, button_text_translations.value)"), 'like', "%{$search}%");
            });
        }
        if (!empty($platform)) {
            $slider->where('platform', $platform);
        }
        // Apply sorting and pagination
        // Return the result
        return $slider
            ->with('related_translations')
            ->orderBy($sortField, $sort)
            ->paginate($limit);
    }

    public function store(array $data)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        try {
            $data['created_by'] = auth('api')->id();
            $data = Arr::except($data, ['translations']);
            $slider = $this->slider->create($data);
            return $slider->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getSliderById(int|string $id)
    {
        try {
            $slider = $this->slider->with('related_translations')->find($id);
            if ($slider) {
                return response()->json(new AdminSliderDetailsResource($slider), 200);
            } else {
                return response()->json([
                    "massage" => "Data was not found"
                ], 404);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        try {
            $slider = $this->slider->findOrFail($data['id']);
            if ($slider) {
                $data['updated_by'] = auth('api')->id();
                $data = Arr::except($data, ['translations']);
                $slider->update($data);
                return $slider->id;
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
            $slider = Slider::findOrFail($id);
            $this->deleteTranslation($slider->id, Slider::class);
            $slider->delete();
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

    public function changeStatus(int $id)
    {
        $slider = Slider::find($id);
        if ($slider) {
            $slider->status = !$slider->status;
            $slider->save();
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Slider'])
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Slider'])
            ], 200);
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