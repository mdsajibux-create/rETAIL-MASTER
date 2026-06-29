<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface AllSliderManageInterface
{
    public function getPaginatedSlider(int|string $limit, string $language, string $search, string $sortField, string $sort, string $platform);

    public function store(array $data);

    public function update(array $data);

    public function getSliderById(int|string $id);

    public function changeStatus(int $id);

    public function delete(int|string $id);

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function translationKeys();
}