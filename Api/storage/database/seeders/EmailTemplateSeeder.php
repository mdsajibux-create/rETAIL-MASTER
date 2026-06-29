<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\SystemCore\app\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Batch insert email templates
        $templates = [
            [
                "type" => 'register',
                "name" => 'User Registration',
                "subject" => 'Welcome to ' . config('app.name'),
                "body" => "<h1>Welcome @name!</h1>
                            <p>Thank you for joining @site_name.</p>
                            <ul>
                                <li>Name: @name</li>
                                <li>Email: @email</li>
                                <li>Phone: @phone</li>
                            </ul>
                          ",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'register',
                "name" => 'User Registration',
                "subject" => 'Welcome to ' . config('app.name'),
                "body" => "<h1>Hello Admin, A New Seller Just Joined BravoMart!</h1>
                            <ul>
                                <li>Name: @name</li>
                                <li>Email: @email</li>
                                <li>Phone: @phone</li>
                            </ul>
                          ",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'password-reset',
                "name" => 'Password Reset',
                "subject" => 'Reset Your Password for ' . config('app.name'),
                "body" => "<h1>Hello @name,</h1>
                            <p>We received a request to reset your password. Use this code:</p>
                            <h2>@reset_code</h2>
                            <p>If this wasn’t you, please ignore this email.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'store-creation',
                "name" => 'New Store Created',
                "subject" => 'A New Store Has Been Created on ' . config('app.name'),
                "body" => "<h1>Hello, @owner_name,</h1>
                           <p>Your store <strong>@store_name</strong> has been successfully created!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'subscription-expired',
                "name" => 'Subscription Expired Notification',
                "subject" => 'Your Subscription Has Expired!',
                "body" => "<h1>Hello, @owner_name,</h1>
                           <p>Your subscription for the store <strong>@store_name</strong> has expired on @expiry_date.</p>
                           <p>Please renew your subscription to continue using our services.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'subscription-renewed',
                "name" => 'Subscription Renewal Confirmation',
                "subject" => 'Your Subscription Has Been Successfully Renewed!',
                "body" => "<h1>Hello, @owner_name,</h1>
                           <p>Your subscription for the store <strong>@store_name</strong> has been successfully renewed.</p>
                           <p>New Expiry Date: @new_expiry_date</p>
                           <p>Thank you for staying with us!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **New Order Created**
            [
                "type" => 'order-created',
                "name" => 'New Order Created',
                "subject" => 'You Have a New Order!',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) has been successfully placed.</p>
                           <p>Order Amount: @order_amount</p>
                           <p>We will notify you once your order status changes.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Store Notification on Order Creation**
            [
                "type" => 'order-created-store',
                "name" => 'New Order Created for Your Store',
                "subject" => 'You Have a New Order in Your Store!',
                "body" => "<h1>Hello @store_owner_name,</h1>
                           <p>Your store <strong>@store_name</strong> has received a new order (Order ID: @order_id).</p>
                           <p>Order Amount: @order_amount</p>
                           <p>Please process the order as soon as possible.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Admin Notification on Order Creation**
            [
                "type" => 'order-created-admin',
                "name" => 'New Order Created',
                "subject" => 'New Order Placed on the Platform!',
                "body" => "<h1>Hello Admin,</h1>
                           <p>A new order (Order ID: @order_id) has been placed on the platform.</p>
                           <p>Order Amount: @order_amount</p>
                           <p>Please review the order details and take necessary action.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Pending)**
            [
                "type" => 'order-status-pending',
                "name" => 'Order Pending Notification',
                "subject" => 'Your Order Status: Pending',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) is now <strong>pending</strong>.</p>
                           <p>We will notify you once the order status changes.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Confirmed)**
            [
                "type" => 'order-status-confirmed',
                "name" => 'Order Confirmed Notification',
                "subject" => 'Your Order Status: Confirmed',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) has been <strong>confirmed</strong>!</p>
                           <p>We will notify you once it is processed.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Processing)**
            [
                "type" => 'order-status-processing',
                "name" => 'Order Processing Notification',
                "subject" => 'Your Order Status: Processing',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) is now <strong>being processed</strong>.</p>
                           <p>We will notify you once it is shipped.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Shipped)**
            [
                "type" => 'order-status-shipped',
                "name" => 'Order Shipped Notification',
                "subject" => 'Your Order Status: Shipped',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) has been <strong>shipped</strong>!</p>
                           <p>It is on its way to you and will arrive soon.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Delivered)**
            [
                "type" => 'order-status-delivered',
                "name" => 'Order Delivered Notification',
                "subject" => 'Your Order Has Been Delivered!',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) has been <strong>delivered</strong>!</p>
                           <p>We hope you enjoy your purchase!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Order Status Update (Cancelled)**
            [
                "type" => 'order-status-cancelled',
                "name" => 'Order Cancelled Notification',
                "subject" => 'Your Order Has Been Cancelled',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your order (Order ID: @order_id) has been <strong>cancelled</strong>.</p>
                           <p>If you have any questions, please contact our support team.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Refund Notification to Customer**
            [
                "type" => 'refund-customer',
                "name" => 'Refund Processed',
                "subject" => 'Your Refund has Been Processed',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your refund for Order ID: @order_id has been successfully processed.</p>
                           <p>Refund Amount: @refund_amount</p>
                           <p>The amount will be credited back to your account shortly.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Refund Notification to Store**
            [
                "type" => 'refund-store',
                "name" => 'Refund Processed for Your Store',
                "subject" => 'A Refund has Been Processed for an Order in Your Store',
                "body" => "<h1>Hello @store_owner_name,</h1>
                           <p>A refund has been processed for an order in your store (Order ID: @order_id).</p>
                           <p>Refund Amount: @refund_amount</p>
                           <p>Please ensure your account is updated accordingly.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Customer Adds Wallet Balance**
            [
                "type" => 'wallet-balance-added-customer',
                "name" => 'Customer Wallet Balance Added',
                "subject" => 'Your Wallet Balance Has Been Updated',
                "body" => "<h1>Hello @customer_name,</h1>
                           <p>Your wallet balance has been successfully updated.</p>
                           <p>New Balance: @balance</p>
                           <p>Thank you for using our service!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Seller Store Adds Wallet Balance**
            [
                "type" => 'wallet-balance-added-store',
                "name" => 'Store Wallet Balance Added',
                "subject" => 'Your Store Wallet Balance Has Been Updated',
                "body" => "<h1>Hello @store_owner_name,</h1>
                           <p>Your store's wallet balance has been successfully updated.</p>
                           <p>Store: @store_name</p>
                           <p>New Balance: @balance</p>
                           <p>Thank you for being a part of our platform!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Store Withdrawal Request to Admin**
            [
                "type" => 'store-withdrawal-request',
                "name" => 'Store Withdrawal Request',
                "subject" => 'A Withdrawal Request Has Been Submitted',
                "body" => "<h1>Hello Admin,</h1>
                           <p>A withdrawal request has been submitted by @store_owner_name for their store <strong>@store_name</strong>.</p>
                           <p>Requested Amount: @amount</p>
                           <p>Please review and take the necessary action.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Admin Approves Withdrawal Request**
            [
                "type" => 'store-withdrawal-approved',
                "name" => 'Store Withdrawal Approved',
                "subject" => 'Your Withdrawal Request Has Been Approved',
                "body" => "<h1>Hello @store_owner_name,</h1>
                           <p>Your withdrawal request for your store <strong>@store_name</strong> has been approved.</p>
                           <p>Amount: @amount</p>
                           <p>The amount will be transferred to your account shortly.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // **Admin Declines Withdrawal Request**
            [
                "type" => 'store-withdrawal-declined',
                "name" => 'Store Withdrawal Declined',
                "subject" => 'Your Withdrawal Request Has Been Declined',
                "body" => "<h1>Hello @store_owner_name,</h1>
                           <p>Your withdrawal request for your store <strong>@store_name</strong> has been declined.</p>
                           <p>Amount: @amount</p>
                           <p>If you have any questions, please contact the support team.</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "type" => 'deliveryman-withdrawal-request',
                "name" => 'Deliveryman Withdrawal Request',
                "subject" => 'Your Withdrawal Request Has Been Received',
                "body" => "<h1>Hello @deliveryman_name,</h1>
                       <p>Your withdrawal request has been successfully submitted for the amount of @amount.</p>
                       <p>Your request is being reviewed by the admin. You will receive a confirmation email once your request has been processed.</p>
                       <p>Thank you for your hard work!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            // ** Order Status Update Deliveryman (delivered) **
            [
                "type" => 'deliveryman-earning',
                "name" => 'Delivery Earnings Notification',
                "subject" => 'You Have New Earnings!',
                "body" => "<h1>Hello, @deliveryman_name,</h1>
                            <p>You've received a new earning:</p>
                            <p><strong>Order ID:</strong> @order_id</p>
                            <p><strong>Order Amount:</strong> @order_amount</p>
                            <p><strong>Earnings:</strong> @earnings_amount</p>
                            <p>Thank you for your hard work!</p>",
                "status" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
        ];

        // Single query for multiple records
        EmailTemplate::insert($templates);

    }
}
