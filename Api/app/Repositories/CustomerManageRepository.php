<?php

namespace App\Repositories;

use App\Interfaces\CustomerManageInterface;
use App\Mail\EmailVerificationMail;
use App\Models\Customer;
use App\Models\Wishlist;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Order\app\Models\Order;
use Modules\SupportTicket\app\Models\Ticket;
use Modules\Wallet\app\Models\Wallet;

class CustomerManageRepository implements CustomerManageInterface
{


    public function __construct(protected Customer $customer)
    {

    }

    public function register(array $data)
    {
        try {
            return $this->customer->create($data);
        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                "status" => false,
                "status_code" => 500,
                "message" => __('messages.error'),
            ], 500);
        }
    }

    public function sendVerificationEmail(string $email)
    {
        $customer = $this->customer->where('email', $email)->first();

        if (!$customer) {
            return false;
        }

        try {
            $token = rand(100000, 999999);
            $customer->email_verify_token = $token;
            $customer->save();
            // Send email verification
            Mail::to($customer->email)->send(new EmailVerificationMail($customer));

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
            ]);
        }
    }

    public function verifyEmail(string $token)
    {
        $customer = $this->customer->where('email_verify_token', $token)->first();

        if (!$customer) {
            return false;
        }

        try {
            $customer->email_verified = 1;
            $customer->email_verified_at = now();
            $customer->email_verify_token = null;
            $customer->save();

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
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
        $customer = $this->customer->where('email_verify_token', $token)->first();

        if (!$customer) {
            return false;
        }

        try {
            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
            ]);
        }
    }

    public function resetPassword(array $data)
    {
        $customer = $this->customer
            ->where('email', $data['email'])
            ->where('email_verify_token', $data['token'])
            ->first();

        if (!$customer) {
            return false;
        }

        try {
            $customer->password = Hash::make($data['password']);
            $customer->password_changed_at = now();
            $customer->email_verify_token = null;
            $customer->save();

            return true;
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "status_code" => 500,
            ]);
        }
    }

    public function changePassword(array $data)
    {
        $customer = $this->customer->where('email', auth('api_customer')->user()->email)->first();

        if (!$customer || !Hash::check($data['old_password'], $customer->password)) {
            return 'incorrect_old_password';
        }

        try {
            $customer->password = Hash::make($data['new_password']);
            $customer->password_changed_at = now();
            $customer->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function activateAccount()
    {
        $user = auth('api_customer')->user();

        // Activate the account by setting the 'deactivated_at' field to null
        $user->update([
            'deactivated_at' => null,
        ]);

        return true;
    }

    public function deleteAccount()
    {
        $user = auth('api_customer')->user();
        $user->delete(); // Soft delete
        $user->currentAccessToken()->delete();
        return true;
    }

    public function getDashboard()
    {
        $customer_id = auth('api_customer')->user()->id;
        return [
            'wishlist_count' => $this->getWishlistCount($customer_id),
            'total_orders' => $this->getTotalOrders($customer_id),
            'pending_orders' => $this->getPendingOrders($customer_id),
            'canceled_orders' => $this->getCanceledOrders($customer_id),
            'on_hold_products' => $this->getOnHoldProducts($customer_id),
            'wallet' => $this->getWalletAmount($customer_id),
            'total_support_ticket' => $this->getSupportTickets($customer_id),
            'recent_orders' => $this->getRecentOrders($customer_id),
        ];
    }

    protected function getWishlistCount($customer_id)
    {
        return Wishlist::where('customer_id', $customer_id)->count();
    }

    protected function getTotalOrders($customer_id)
    {
        return Order::where('customer_id', $customer_id)->count();
    }

    protected function getPendingOrders($customer_id)
    {
        return Order::where('customer_id', $customer_id)->where('status', 'pending')->count();
    }

    protected function getCanceledOrders($customer_id)
    {
        return Order::where('customer_id', $customer_id)->where('status', 'cancelled')->count();
    }

    protected function getOnHoldProducts($customer_id)
    {
        return Order::where('customer_id', $customer_id)->where('status', 'on_hold')->count();
    }

    protected function getRecentOrders($customer_id)
    {
        return Order::with('customer', 'orderDetail.product', 'deliveryman', 'shippingAddress')
            ->where('customer_id', $customer_id)
            ->latest()
            ->limit(10)
            ->get();
    }

    protected function getWalletAmount($customer_id)
    {
        $exists = Wallet::where('owner_id', $customer_id);
        if (!$exists) {
            return null;
        }
        $isCustomer = $exists->where('owner_type', 'App\Models\Customer')
            ->where('status', 1)
            ->first();
        if ($isCustomer) {
            return $isCustomer->balance;
        } else {
            return null;
        }
    }

    protected function getSupportTickets($customer_id)
    {
        $tickets = Ticket::where('user_id', $customer_id)->get();

        if (empty($tickets)) {
            return null;
        } else {
            return $tickets->count();
        }
    }

    public function deleteCustomerRelatedAllData(int $customer_id): bool
    {
        $customer = Customer::find($customer_id);
        if (!$customer) {
            return false;
        }
        DB::transaction(function () use ($customer) {
            // Delete support ticket files
            foreach ($customer->tickets as $ticket) {
                foreach ($ticket->messages as $message) {
                    if ($message->file && file_exists(public_path($message->file))) {
                        @unlink(public_path($message->file));
                    }
                }
            }

            $customer->blogComments()->delete();
            $customer->reviews()->delete();
            $customer->tickets()->delete();
            $customer->sentMessages()->delete();
            $customer->receivedMessages()->delete();
            $customer->productQueries()->delete();
            $customer->wishlists()->delete();
            $customer->blogCommentReactions()->delete();
            $customer->reviewReactions()->delete();
            $customer->addresses()->delete();
            $customer->userOtps()->delete();
            $customer->notifications()->delete();

            // Delete profile image if used MediaService
            $imgId[] = $customer->image;
            if ($customer->image) {
                app(MediaService::class)->bulkDeleteMediaImages($imgId);
            }

            $customer->delete();

        });


        return true;
    }

}
