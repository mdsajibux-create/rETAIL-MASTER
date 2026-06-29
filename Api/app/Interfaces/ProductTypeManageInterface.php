<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface ProductTypeManageInterface
{
    public function getAllStoreTypes(array $filters);

    public function getStoreTypeById(int $id);

    public function updateStoreType(array $data);
    public function toogleStatus(int $id);
    public function createOrUpdateTranslation(Request $request, int|string $refid, string $refPath, array $colNames);
}
