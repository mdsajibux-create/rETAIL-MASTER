<?php

namespace App\Interfaces;


use Illuminate\Http\Request;

interface DeliverymanManageInterface
{
    public function change_password(int $deliveryman_id, string $password);

    public function update(array $data);

    public function getAllDeliveryman(array $filters);

    public function getDeliverymanById(int $id);

    public function delete(int $userId);

    public function getDeliverymanRequests();

    public function approveDeliverymen(array $deliveryman_ids);

    public function rejectDeliverymen(array $deliveryman_ids);

    public function changeStatus(array $data);

    public function getAllVehicles(array $filters);

    public function toggleVehicleStatus(int $id);

    public function addVehicle(array $data);

    public function updateVehicle(array $data);

    public function getVehicleById(int $id);

    public function vehicleTypeDropdown();

    public function deleteVehicle(int $id);

    public function deliverymanOrders($filters);

    public function deliverymanOrderDetails(int $order_id);

    public function orderRequests();

    public function updateOrderStatus(string $status, int $order_id, string $reason);

    public function orderChangeStatus(string $status, int $order_id);

    public function deliverymanOrderHistory();

    public function deliverymanListDropdown(array $filter);

    public function getDeliverymanDashboard(?int $deliveryman_id);

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames);

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames);
}
