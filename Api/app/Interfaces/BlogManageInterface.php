<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface BlogManageInterface
{
    public function translationKeysForCategory();
    public function getPaginatedCategory(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters);
    public function getCategoryById(int|string $id);
    public function translationKeysForBlog();
    public function getPaginatedBlog(int|string $per_page, int $page, string $language, string $search, string $sortField, string $sort, array $filters);
    public function store(array $data, string $modelClass);
    public function update(array $data, string $modelClass);
    public function delete(int|string $id, string $modelClass);
    public function storeTranslation(Request $request, int|string $refid, string $refPath, array  $colNames);
    public function updateTranslation(Request $request, int|string $refid, string $refPath, array  $colNames);

}
