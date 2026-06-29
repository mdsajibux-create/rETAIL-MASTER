<?php

namespace Modules\Pos\app\Interfaces;

use Illuminate\Http\Request;

interface PosInterface
{
    public function getStoreCustomers($branch_id , $filters);

    public function createNewCustomer(Request $request);

    public function getProducts(Request $request);

    public function getProductBySlug(Request $request, $slug);

    public function getOrders(Request $request);
}