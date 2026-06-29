<?php

namespace App\Http\Controllers\Api\V1\Deliveryman;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\DeliverymanDashboardResource;
use App\Interfaces\DeliverymanManageInterface;

class DeliverymanDashboardController extends Controller
{
    public function __construct(protected DeliverymanManageInterface $deliverymanRepo)
    {

    }

    public function dashboard()
    {
        $data = $this->deliverymanRepo->getDeliverymanDashboard();
        return response()->json(new DeliverymanDashboardResource((object)$data));
    }
}
