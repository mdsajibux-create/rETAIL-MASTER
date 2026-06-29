<?php

namespace App\Repositories;

use Modules\Coupon\app\Models\CouponLine;

class CouponLineManageRepository
{
    public function __construct(protected CouponLine $couponLine)
    {
    }

    public function getPaginatedCouponLines(int|string $limit, int $page, string $search, string $sortField, string $sort, array $filters)
    {
        $couponLines = CouponLine::query();

        // Apply search filter if search parameter exists
        if ($search) {
            $couponLines->where(function ($query) use ($search) {
                $query->where('coupon_code', 'like', "%{$search}%")
                    ->orWhere('discount_type', 'like', "%{$search}%");
            });
        }
        // Apply additional filters
        foreach ($filters as $key => $value) {
            $couponLines->where($key, $value);
        }
        // Apply sorting and pagination
        $couponLines = $couponLines->orderBy($sortField, $sort)->paginate($limit);
        return $couponLines;
    }

    public function couponLineStore(array $data)
    {
        try {
            $couponLine = CouponLine::create($data);
            return $couponLine->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getCouponLineById(int|string $id)
    {
        try {
            $couponLine = CouponLine::findOrFail($id);
            return $couponLine;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function couponLineUpdate(array $data)
    {
        try {
            $couponLine = CouponLine::findOrFail($data['id']);
            $couponLine->update($data);
            return $couponLine->id;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function couponLineDelete(int|string $id)
    {
        try {
            $couponLine = CouponLine::findOrFail($id);
            $couponLine->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
