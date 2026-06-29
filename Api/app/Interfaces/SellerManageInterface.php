<?php

namespace App\Interfaces;
interface SellerManageInterface
{
    public function register(array $data);
    public function sendVerificationEmail(string $email);
    public function verifyEmail(string $token);
    public function resendVerificationEmail(string $email);
    public function forgetPassword(string $email);
    public function verifyToken(string $token);
    public function resetPassword(array $data);
    public function deactivateAccount();
    public function deleteAccount();
}