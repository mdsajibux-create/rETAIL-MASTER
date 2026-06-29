<?php

namespace App\Repositories;

use App\Interfaces\SellerManageInterface;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SellerManageRepository implements SellerManageInterface
{
    public function __construct(protected User $seller)
    {

    }

    public function register(array $data)
    {
        try {
            return $this->seller->create($data);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => __('messages.error'),
                "error" => $e->getMessage(),
            ], 500);
        }
    }

    public function sendVerificationEmail(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        try {
            $token = rand(100000, 999999);
            $user->email_verify_token = $token;
            $user->save();
            // Send email verification
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function verifyEmail(string $token)
    {
        $seller = $this->seller->where('email_verify_token', $token)->first();

        if (!$seller) {
            return false;
        }

        try {
            $seller->email_verified = 1;
            $seller->email_verified_at = now();
            $seller->email_verify_token = null;
            $seller->save();

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    // Resend the verification email
    public function resendVerificationEmail(string $email)
    {
        return $this->sendVerificationEmail($email);
    }

    public function forgetPassword(string $email)
    {
        return $this->sendVerificationEmail($email);
    }

    public function verifyToken(string $token)
    {
        $seller = $this->seller->where('email_verify_token', $token)->first();

        if (!$seller) {
            return false;
        }

        try {
            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function resetPassword(array $data)
    {
        $seller = $this->seller
            ->where('email', $data['email'])
            ->where('email_verify_token', $data['token'])
            ->first();

        if (!$seller) {
            return false;
        }

        try {
            $seller->password = Hash::make($data['password']);
            $seller->password_changed_at = now();
            $seller->email_verify_token = null;
            $seller->save();

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => $e->getMessage()
            ]);
        }
    }

    public function deactivateAccount()
    {
        $user = auth('api_customer')->user();
        $user->update([
            'status' => 0,
            'deactivated_at' => now(),
        ]);
        $user->currentAccessToken()->delete();
        return true;
    }

    public function deleteAccount()
    {
        $user = auth('api_customer')->user();
        $user->delete(); // Soft delete
        $user->currentAccessToken()->delete();
        return true;
    }

}
