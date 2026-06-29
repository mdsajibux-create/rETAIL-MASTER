<?php

namespace App\Repositories;

use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\CouponDetailsResource;
use App\Http\Resources\CouponLineResource;
use App\Http\Resources\CouponResource;
use App\Interfaces\CouponApplyManageInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Coupon\app\Models\Coupon;
use Modules\Coupon\app\Models\CouponLine;

class CouponApplyManageRepository implements CouponApplyManageInterface
{
    public function __construct(protected Coupon $coupon, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->coupon->translationKeys;
    }

    public function model(): string
    {
        return Coupon::class;
    }

    public function getPaginatedCoupon(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters)
    {
        $coupon = Coupon::leftJoin('translations as title_translations', function ($join) use ($language) {
            $join->on('coupons.id', '=', 'title_translations.translatable_id')
                ->where('title_translations.translatable_type', '=', Coupon::class)
                ->where('title_translations.language', '=', $language)
                ->where('title_translations.key', '=', 'title');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('coupons.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Coupon::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->select(
                'coupons.*',
                DB::raw('COALESCE(title_translations.value, coupons.title) as title'),
                DB::raw('COALESCE(description_translations.value, coupons.description) as description')
            );
        // Apply search filter if search parameter exists
        if ($search) {
            $coupon->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', coupons.title, name_translations.value, coupons.description, description_translations.value)"), 'like', "%{$search}%");
            });
        }
        // Apply sorting and pagination
        // Return the result
        $coupons = $coupon
            ->with(['creator','related_translations'])
            ->orderBy($sortField, $sort)
            ->paginate($limit);
        return response()->json([
            'coupons' => CouponResource::collection($coupons),
            'meta' => new PaginationResource($coupons),
        ]);
    }

    public function coupon_wise_coupon_line(int $coupon_id)
    {
        $coupon_line = CouponLine::where('coupon_id', $coupon_id)->get();
        return response()->json([
            'coupon_line' => CouponLineResource::collection($coupon_line),
        ]);
    }

    public function store(array $data)
    {
        $data['created_by'] = auth('api')->id();
        try {
            $data = Arr::except($data, ['translations']);
            $coupon = Coupon::create($data);
            return $coupon->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getCouponById(int|string $id)
    {
        try {
            $coupon = Coupon::with(['creator', 'related_translations'])->findorfail($id);
            if ($coupon) {
                return response()->json(new CouponDetailsResource($coupon));
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
        $data['created_by'] = auth('api')->id();
        try {
            $coupon = Coupon::findOrFail($data['id']);
            if ($coupon) {
                $data = Arr::except($data, ['translations']);
                $coupon->update($data);
                return $coupon->id;
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
            $coupon = Coupon::findOrFail($id);
            $this->deleteTranslation($coupon->id, Coupon::class);
            $coupon->delete();
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
