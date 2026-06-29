<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface TranslationInterface  extends RepositoryInterface
{
    public function storeTranslation(Request $request, int|string $refid, string $refPath,Array  $colNames): bool;
    public function updateTranslation(Request $request, int|string $refid, string $refPath,Array  $colNames): bool;
}
