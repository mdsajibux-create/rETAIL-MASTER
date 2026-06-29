<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface BranchManageInterface
{
    public function branchList(int|string $limit, int|string $status, int $page, string $language, string $search, string $sortField, string $sort, array $filters);

    public function store(array $data);

    public function update(array $data);

    public function delete(int|string $id);

    public function getBranchById(int|string $id);

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function translationKeys();

    public function records(bool $onlyDeleted);

    public function getSummaryData(string $slug, ?int $seller_id);

    public function getSalesSummaryData(array $filters, ?string $slug);

    public function getOtherSummaryData(?string $slug);

    public function getOrderGrowthData(?string $slug = null);

    public function changeStatus(array $data);
}
