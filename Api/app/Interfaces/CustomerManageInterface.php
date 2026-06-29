<?php

namespace App\Interfaces;


interface CustomerManageInterface
{
    public function register(array $data);

    public function sendVerificationEmail(string $email);

    public function verifyEmail(string $token);

    public function resendVerificationEmail(string $email);

    public function forgetPassword(string $email);

    public function verifyToken(string $token);

    public function resetPassword(array $data);

    public function changePassword(array $data);

    public function activateAccount();

    public function deleteAccount();

    public function getDashboard();

    public function deleteCustomerRelatedAllData(int $customer_id);
}