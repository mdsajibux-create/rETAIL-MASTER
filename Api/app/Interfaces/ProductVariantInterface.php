<?php

namespace App\Interfaces;
use Illuminate\Http\Request;
interface ProductVariantInterface{
    public function getPaginatedVariant(int|string $limit, int $page, string $language, string $search, string $sortField, string $sort, array $filters);
    public function store(array $data);
    public function update(array $data);
    public function delete(int|string $id);
    public function getVariantById(int|string $id);
    public function records(bool $onlyDeleted);
}