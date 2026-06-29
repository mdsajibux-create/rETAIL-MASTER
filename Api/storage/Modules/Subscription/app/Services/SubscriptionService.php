<?php

namespace Modules\Subscription\app\Services;

use App\Mail\DynamicEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Branch\app\Models\Branch;
use Modules\Subscription\app\Models\StoreSubscription;
use Modules\Subscription\app\Models\Subscription;
use Modules\Subscription\app\Models\SubscriptionHistory;
use Modules\SystemCore\app\Models\EmailTemplate;
use Modules\Wallet\app\Models\Wallet;


class SubscriptionService
{
    public function buySubscriptionPackage($data)
    {

        $store_id = $data['store_id'];
        $subscription_id = $data['subscription_id'];
        $payment_gateway = $data['payment_gateway'];

        // Find the store
        $store = Branch::find($store_id);

        if (!$store) {
            return [
                'success' => false,
                'message' => 'Store not found.',
            ];
        }

        // Find the subscription package
        $subscription_package = Subscription::where('id', $subscription_id)
            ->where('status', 1)
            ->first();

        // If package not found
        if (empty($subscription_package)) {
            return [
                'success' => false,
                'message' => 'Subscription package not found.',
            ];
        }

        // Set default values for payment status
        $subscription_status = 0;
        $payment_status = 'pending';

        // check if the package is trial
        if ((float) $subscription_package->price === 0.0) {
            $already_claimed_trial_package = SubscriptionHistory::where('store_id', $store_id)
                ->where('subscription_id', $subscription_package->id)
                ->first();

            if ($already_claimed_trial_package) {
                return [
                    'success' => false,
                    'message' => 'Trial already used. Upgrade now to continue enjoying premium features!',
                ];
            }
            $subscription_status = 1;
            $payment_status = 'paid';
            // Update store status
            $store->status = 1;
            $store->save();
        }

        // Check payment gateway (wallet or others)
        if ($payment_gateway == 'wallet') {
            // wallet balance check
            $store_wallet_balance = Wallet::where('owner_type', 'Modules\Branch\app\Models\Branch')
                ->where('owner_id', $store->id)
                ->first();

            if (!empty($store_wallet_balance) && $store_wallet_balance->balance >= $subscription_package->price) {
                // Proceed with subscription purchase
                $subscription_status = 1;
                $payment_status = 'paid';

                // Update store status
                $store->status = 1;
                $store->save();

                // Decrement wallet balance
                $store_wallet_balance->balance -= $subscription_package->price;
                $store_wallet_balance->save();


            } else {
                return [
                    'success' => false,
                    'message' => 'Insufficient wallet balance. Please deposit funds to continue.',
                ];
            }

        }

        // Check for existing subscription and update if found
        $existing_subscription = StoreSubscription::where('store_id', $store_id)->first();

        if (isset($existing_subscription->store_id) && !empty($existing_subscription)) {
            // if subscription not expire add old value
            if ($existing_subscription->expire_date > now()) {
                // Not expired – accumulate limits
               $order_limit = $existing_subscription->order_limit + $subscription_package->order_limit;
               $product_limit = $existing_subscription->product_limit + $subscription_package->product_limit;
               $product_featured_limit = $existing_subscription->product_featured_limit + $subscription_package->product_featured_limit;
               $validity = $existing_subscription->validity + $subscription_package->validity;
            }else{
                // Expired or no subscription – reset to new package limits
                $order_limit = $subscription_package->order_limit;
                $product_limit = $subscription_package->product_limit;
                $product_featured_limit = $subscription_package->product_featured_limit;
                $validity = $subscription_package->validity;
            }

            // Calculate new validity based on current expire date
            $new_validity = $subscription_package->validity;

            // Convert the expire_date to a Carbon instance to use addDays()
            $existing_expiry_date = \Carbon\Carbon::parse($existing_subscription->expire_date);

            // Extend the subscription validity by adding the new validity to the current expire date
            $new_expire_date = $existing_expiry_date->addDays((int)$new_validity);

            // Update the existing subscription
            $existing_subscription->subscription_id = $subscription_package->id;
            $existing_subscription->name = $subscription_package->name;
            $existing_subscription->type = $subscription_package->type;
            $existing_subscription->pos_system = $subscription_package->pos_system;
            $existing_subscription->self_delivery = $subscription_package->self_delivery;
            $existing_subscription->mobile_app = $subscription_package->mobile_app;
            $existing_subscription->live_chat = $subscription_package->live_chat;
            $existing_subscription->order_limit = $order_limit;
            $existing_subscription->product_limit = $product_limit;
            $existing_subscription->product_featured_limit = $product_featured_limit;
            $existing_subscription->validity = $validity;
            $existing_subscription->expire_date = $new_expire_date;
            $existing_subscription->status = $subscription_status;
            $existing_subscription->payment_status = $payment_status;
            $existing_subscription->payment_gateway = $payment_gateway;
            $existing_subscription->save();

            // Create subscription history
            SubscriptionHistory::create([
                'store_subscription_id' => $existing_subscription->id,
                'store_id' => $store_id,
                'subscription_id' => $subscription_package->id,
                'name' => $subscription_package->name,
                'type' => $subscription_package->type,
                'validity' => $subscription_package->validity,
                'price' => $subscription_package->price,
                'pos_system' => $subscription_package->pos_system,
                'self_delivery' => $subscription_package->self_delivery,
                'mobile_app' => $subscription_package->mobile_app,
                'live_chat' => $subscription_package->live_chat,
                'order_limit' => $subscription_package->order_limit,
                'product_limit' => $subscription_package->product_limit,
                'product_featured_limit' => $subscription_package->product_featured_limit,
                'payment_gateway' => $payment_gateway ?? null,
                'payment_status' => $payment_status ?? null,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => $new_expire_date,
                'status' => $subscription_status,
            ]);

        } else {
            // Create a new subscription if no existing one found
            $store_sub = StoreSubscription::create([
                'store_id' => $store_id,
                'subscription_id' => $subscription_package->id,
                'name' => $subscription_package->name,
                'type' => $subscription_package->type,
                'validity' => $subscription_package->validity,
                'price' => $subscription_package->price,
                'pos_system' => $subscription_package->pos_system,
                'self_delivery' => $subscription_package->self_delivery,
                'mobile_app' => $subscription_package->mobile_app,
                'live_chat' => $subscription_package->live_chat,
                'order_limit' => $subscription_package->order_limit,
                'product_limit' => $subscription_package->product_limit,
                'product_featured_limit' => $subscription_package->product_featured_limit,
                'payment_gateway' => $payment_gateway ?? null,
                'payment_status' => $payment_status ?? null,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => now()->addDays((int)$subscription_package->validity),
                'status' => $subscription_status,
            ]);
            // Create subscription history
            SubscriptionHistory::create([
                'store_subscription_id' => $store_sub->id,
                'store_id' => $store_id,
                'subscription_id' => $subscription_package->id,
                'name' => $subscription_package->name,
                'type' => $subscription_package->type,
                'validity' => $subscription_package->validity,
                'price' => $subscription_package->price,
                'pos_system' => $subscription_package->pos_system,
                'self_delivery' => $subscription_package->self_delivery,
                'mobile_app' => $subscription_package->mobile_app,
                'live_chat' => $subscription_package->live_chat,
                'order_limit' => $subscription_package->order_limit,
                'product_limit' => $subscription_package->product_limit,
                'product_featured_limit' => $subscription_package->product_featured_limit,
                'payment_gateway' => $payment_gateway ?? null,
                'payment_status' => $payment_status ?? null,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => now()->addDays((int)$subscription_package->validity),
                'status' => $subscription_status,
            ]);
        }

        $store->update(['subscription_type' => 'subscription']);


        // send mail and notification
        $store_email = $store->email;
        $system_global_email = com_option_get('com_site_email');

        // subscription buy mail send
        try {
            $email_template_subscription_store = EmailTemplate::where('type', 'subscription-buy-store')->where('status', 1)->first();
            $email_template_subscription_admin = EmailTemplate::where('type', 'subscription-buy-admin')->where('status', 1)->first();

            //subject
            $store_subject = $email_template_subscription_store->subject;
            $admin_subject = $email_template_subscription_admin->subject;
            //body
            $store_message = $email_template_subscription_store->body;
            $admin_message = $email_template_subscription_admin->body;

            $store_name = $store->name;
            $seller_name = auth()->guard('api')->user()->full_name;

            $subscription_status_label = match ($subscription_status) {
                0 => 'Pending',
                1 => 'Active',
                2 => 'Cancelled',
                default => 'Unknown',
            };

            $store_message = str_replace(["@seller_name", "@store_name", "@subscription_name", "@validity_days", "@expiry_date", "@payment_status", "@subscription_status"],
                [
                    $seller_name,
                    $store_name,
                    $subscription_package->name,
                    $subscription_package->validity,
                    $new_expire_date ?? now()->addDays((int)$subscription_package->validity),
                    $payment_status,
                    $subscription_status_label
                ], $store_message);

            $admin_message = str_replace(["@seller_name", "@store_name", "@subscription_name", "@validity_days", "@expiry_date", "@payment_status", "@subscription_status"],
                [
                    $seller_name,
                    $store_name,
                    $subscription_package->name,
                    $subscription_package->validity,
                    $new_expire_date ?? now()->addDays((int)$subscription_package->validity),
                    $payment_status,
                    $subscription_status_label
                ], $admin_message);

            // store
            Mail::to($store_email)->send(new DynamicEmail($store_subject, (string)$store_message));
            // admin
            Mail::to($system_global_email)->send(new DynamicEmail($admin_subject, (string)$admin_message));
        } catch (\Exception $th) {
        }

        return [
            'success' => true,
            'message' => 'Subscription successfully purchased.',
        ];

    }

    public function renewSubscriptionPackage($store_id, $subscription_id, $request_payment_gateway)
    {

        // Authenticate user
        $seller = Auth::guard('api')->user();
        if (!$seller) {
            return [
                'success' => false,
                'message' => __('messages.authorization_invalid'),
            ];
        }

        // Fetch the store
        $store = Branch::find($store_id);
        if (!$store || $store->store_seller_id != $seller->id) {
            return [
                'success' => false,
                'message' => __('messages.store_not_found'),
            ];
        }

        // Ensure store subscription type is valid
        if ($store->subscription_type !== 'subscription') {
            return [
                'success' => false,
                'message' => __('messages.store_subscription_invalid_type'),
            ];
        }

        // Fetch the active subscription
        $currentSubscription = StoreSubscription::where('store_id', $store_id)->first();
        if (!$currentSubscription) {
            return [
                'success' => false,
                'message' => __('messages.store_subscription_no_active_not_found'),
            ];
        }

        // Fetch the subscription package
        $subscriptionPackage = Subscription::where('id', $subscription_id)
            ->where('price', '>', 0)
            ->where('status', 1)
            ->first();

        if (!$subscriptionPackage) {
            return [
                'success' => false,
                'message' => __('messages.store_subscription_not_found'),
            ];
        }

        // Determine payment gateway and status
        $payment_gateway = $request_payment_gateway ?? null;
        $payment_status = $payment_gateway === 'wallet' ? 'paid' : 'pending';
        $subscription_status = $payment_gateway === 'wallet' ? 1 : 0;

        $days = (int) $subscriptionPackage->validity;
        // Calculate the new expiration date
        $newExpireDate = Carbon::parse($currentSubscription->expire_date)->gt(now())
            ? Carbon::parse($currentSubscription->expire_date)->addDays($days)
            : now()->addDays($days);


        // Check payment gateway (wallet)
        if ($payment_gateway === 'wallet') {
            // wallet balance check
            $store_wallet_balance = Wallet::where('owner_type', 'Modules\Branch\app\Models\Branch')
                ->where('owner_id', $store->id)
                ->first();

            if (!empty($store_wallet_balance) && $store_wallet_balance->balance >= $subscriptionPackage->price) {
                // Proceed with subscription purchase
                // Update store status
                $store->status = 1;
                $store->save();

                // Decrement wallet balance
                $store_wallet_balance->balance -= $subscriptionPackage->price;
                $store_wallet_balance->save();


                // Create subscription history
                SubscriptionHistory::create([
                    'store_subscription_id' => $currentSubscription->id,
                    'store_id' => $store_id,
                    'subscription_id' => $subscriptionPackage->id,
                    'name' => $subscriptionPackage->name,
                    'type' => $subscriptionPackage->type,
                    'validity' => $subscriptionPackage->validity,
                    'price' => $subscriptionPackage->price,
                    'pos_system' => $subscriptionPackage->pos_system,
                    'self_delivery' => $subscriptionPackage->self_delivery,
                    'mobile_app' => $subscriptionPackage->mobile_app,
                    'live_chat' => $subscriptionPackage->live_chat,
                    'order_limit' => $subscriptionPackage->order_limit,
                    'product_limit' => $subscriptionPackage->product_limit,
                    'product_featured_limit' => $subscriptionPackage->product_featured_limit,
                    'payment_gateway' => $payment_gateway,
                    'payment_status' => $payment_status,
                    'transaction_ref' => null,
                    'manual_image' => null,
                    'expire_date' => $newExpireDate,
                    'status' => $subscription_status,
                ]);

                // Update or create the current subscription
                $currentSubscription->update([
                    'subscription_id' => $subscriptionPackage->id,
                    'name' => $subscriptionPackage->name,
                    'type' => $subscriptionPackage->type,
                    'validity' => $currentSubscription->validity + $subscriptionPackage->validity,
                    'price' => $subscriptionPackage->price,
                    'pos_system' => $subscriptionPackage->pos_system,
                    'self_delivery' => $subscriptionPackage->self_delivery,
                    'mobile_app' => $subscriptionPackage->mobile_app,
                    'live_chat' => $subscriptionPackage->live_chat,
                    'order_limit' => $currentSubscription->order_limit + $subscriptionPackage->order_limit,
                    'product_limit' => $currentSubscription->product_limit + $subscriptionPackage->product_limit,
                    'product_featured_limit' => $currentSubscription->product_featured_limit + $subscriptionPackage->product_featured_limit,
                    'payment_gateway' => $payment_gateway,
                    'payment_status' => $payment_status,
                    'transaction_ref' => null,
                    'manual_image' => null,
                    'expire_date' => $newExpireDate,
                    'status' => $subscription_status,
                ]);

            } else {
                return [
                    'success' => false,
                    'message' => __('messages.store_subscription_insufficient_balance'),
                ];
            }

        } else {
            // others payment gateway
            // Create subscription history
            SubscriptionHistory::create([
                'store_subscription_id' => $currentSubscription->id,
                'store_id' => $store_id,
                'subscription_id' => $subscriptionPackage->id,
                'name' => $subscriptionPackage->name,
                'type' => $subscriptionPackage->type,
                'validity' => $subscriptionPackage->validity,
                'price' => $subscriptionPackage->price,
                'pos_system' => $subscriptionPackage->pos_system,
                'self_delivery' => $subscriptionPackage->self_delivery,
                'mobile_app' => $subscriptionPackage->mobile_app,
                'live_chat' => $subscriptionPackage->live_chat,
                'order_limit' => $subscriptionPackage->order_limit,
                'product_limit' => $subscriptionPackage->product_limit,
                'product_featured_limit' => $subscriptionPackage->product_featured_limit,
                'payment_gateway' => $payment_gateway,
                'payment_status' => $payment_status,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => $newExpireDate,
                'status' => $subscription_status,
            ]);
        }


        // send mail and notification
        $store_email = $store->email;
        $system_global_email = com_option_get('com_site_email');

        // subscription buy mail send
        try {
            $email_template_subscription_store = EmailTemplate::where('type', 'subscription-renewed-store')->where('status', 1)->first();
            $email_template_subscription_admin = EmailTemplate::where('type', 'subscription-renewed-admin')->where('status', 1)->first();

            //subject
            $store_subject = $email_template_subscription_store->subject;
            $admin_subject = $email_template_subscription_admin->subject;
            //body
            $store_message = $email_template_subscription_store->body;
            $admin_message = $email_template_subscription_admin->body;

            $store_name = $store->name;
            $seller_name = auth()->guard('api')->user()->full_name;

            $subscription_status_label = match ($subscription_status) {
                0 => 'Pending',
                1 => 'Active',
                2 => 'Cancelled',
                default => 'Unknown',
            };

            $store_message = str_replace(["@seller_name", "@store_name", "@subscription_name", "@validity_days", "@expiry_date", "@payment_status", "@subscription_status"],
                [
                    $seller_name,
                    $store_name,
                    $subscriptionPackage->name,
                    $subscriptionPackage->validity,
                    $newExpireDate,
                    $payment_status,
                    $subscription_status_label
                ], $store_message);

            $admin_message = str_replace(["@seller_name", "@store_name", "@subscription_name", "@validity_days", "@expiry_date", "@payment_status", "@subscription_status"],
                [
                    $seller_name,
                    $store_name,
                    $subscriptionPackage->name,
                    $subscriptionPackage->validity,
                    $newExpireDate,
                    $payment_status,
                    $subscription_status_label
                ], $admin_message);

            // store
            Mail::to($store_email)->send(new DynamicEmail($store_subject, (string)$store_message));
            // admin
            Mail::to($system_global_email)->send(new DynamicEmail($admin_subject, (string)$admin_message));
        } catch (\Exception $th) {
        }

        return [
            'success' => true,
            'message' => 'Subscription renewed successfully.',
        ];

    }


    public function adminAssignStoreSubscription($data)
    {
        // Authenticate user
        $seller = Auth::guard('api')->user();
        if (!$seller) {
            return [
                'success' => false,
                'message' => 'User is not authenticated.',
            ];
        }

        $store_id = $data['store_id'];
        $subscription_id = $data['subscription_id'];
        $payment_gateway = $data['payment_gateway'];

        // Find the store
        $store = Branch::find($store_id);
        if (!$store) {
            return [
                'success' => false,
                'message' => 'Store not found.',
            ];
        }

        // Find the subscription package
        $subscription_package = Subscription::where('id', $subscription_id)
            ->where('status', 1)
            ->first();

        // If package not found
        if (empty($subscription_package)) {
            return [
                'success' => false,
                'status_code' => 409,
                'message' => 'Subscription package not found.',
            ];
        }

        // Set default values for payment status
        $subscription_status = 1;
        $payment_status = $data['payment_status'];

        // Check for existing subscription and update if found
        $existing_subscription = StoreSubscription::where('store_id', $store_id)
            ->where('subscription_id', $subscription_package->id)
            ->first();

        if (!$existing_subscription) {
            $store_sub = StoreSubscription::create([
                'store_id' => $store_id,
                'subscription_id' => $subscription_package->id,
                'name' => $subscription_package->name,
                'type' => $subscription_package->type,
                'validity' => $subscription_package->validity,
                'price' => $subscription_package->price,
                'pos_system' => $subscription_package->pos_system,
                'self_delivery' => $subscription_package->self_delivery,
                'mobile_app' => $subscription_package->mobile_app,
                'live_chat' => $subscription_package->live_chat,
                'order_limit' => $subscription_package->order_limit,
                'product_limit' => $subscription_package->product_limit,
                'product_featured_limit' => $subscription_package->product_featured_limit,
                'payment_gateway' => $payment_gateway ?? null,
                'payment_status' => $payment_status ?? null,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => now()->addDays($subscription_package->validity),
                'status' => $subscription_status,
            ]);
            // Create subscription history
            SubscriptionHistory::create([
                'store_subscription_id' => $store_sub->id,
                'store_id' => $store_id,
                'subscription_id' => $subscription_package->id,
                'name' => $subscription_package->name,
                'type' => $subscription_package->type,
                'validity' => $subscription_package->validity,
                'price' => $subscription_package->price,
                'pos_system' => $subscription_package->pos_system,
                'self_delivery' => $subscription_package->self_delivery,
                'mobile_app' => $subscription_package->mobile_app,
                'live_chat' => $subscription_package->live_chat,
                'order_limit' => $subscription_package->order_limit,
                'product_limit' => $subscription_package->product_limit,
                'product_featured_limit' => $subscription_package->product_featured_limit,
                'payment_gateway' => $payment_gateway ?? null,
                'payment_status' => $payment_status ?? null,
                'transaction_ref' => null,
                'manual_image' => null,
                'expire_date' => now()->addDays($subscription_package->validity),
                'status' => $subscription_status,
            ]);
            $store->update(['subscription_type' => 'subscription']);
            return [
                'success' => true,
                'message' => 'Subscription successfully assigned.',
                'status_code' => 201
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Subscription already exists.',
                'status_code' => 409
            ];
        }

        // fallback
        return [
            'success' => false,
            'message' => 'Failed to assign subscription.',
            'status_code' => 500,
        ];

    }


}
