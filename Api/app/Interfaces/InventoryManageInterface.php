<?php

namespace App\Interfaces;
interface InventoryManageInterface
{
    public function getInventories(?array $filters);

    public function deleteProductsWithVariants(array $productIds);

}
