<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface PageManageInterface
{
    public function getPageById(string $slug);
    public function translationKeysForPage();
    public function getPaginatedPage(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters);
    public function store(array $data, string $modelClass);
    public function update(array $data, string $modelClass);
    public function delete(int|string $id, string $modelClass);
    public function storeTranslation(Request $request, int|string $refid, string $refPath, array  $colNames);
    public function updateTranslation(Request $request, int|string $refid, string $refPath, array  $colNames);
}
