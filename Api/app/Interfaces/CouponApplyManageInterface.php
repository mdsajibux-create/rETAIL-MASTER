<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface CouponApplyManageInterface
{
    public function getPaginatedCoupon(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters);

    public function store(array $data);

    public function update(array $data);

    public function delete(int|string $id);

    public function getCouponById(int|string $id);

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function coupon_wise_coupon_line(int $coupon_id);

    public function translationKeys();

}