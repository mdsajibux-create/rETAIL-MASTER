<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface AdminDashboardInterface
{
    public function getSummaryData(?int $branch_id, $filters = []);

    public function getSummaryDataWithFilters(array $filters): array;

    public function getSalesSummaryData(array $filters);

    public function getOtherSummaryData(array $filters = []);

    public function getOrderGrowthData(array $filters = []);
}