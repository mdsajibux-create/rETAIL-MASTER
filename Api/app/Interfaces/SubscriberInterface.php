<?php

namespace App\Interfaces;
interface SubscriberInterface
{
    public function subscribe(array $data);
    public function unsubscribe(string $email);
    public function getSubscribers(array $filters);
    public function changeStatus(array $data);
    public function sendBulkMail(array $data);
    public function delete(int|string $id);
}
