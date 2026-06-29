<?php
namespace Modules\SmsGateway\app\Services\Sms;

interface SmsInterface
{
    public function send(string $to, string $message): bool;
}