<?php

namespace App\Services\Order;

use App\Models\UniversalNotification;
use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\WebPushConfig;
use Modules\Order\app\Models\Order;

class OrderManageNotificationService
{
    private bool $adminNotified = false;

    public function createOrderNotification($last_order_ids, $otherCheckData = null)
    {

        try {
            if (empty($last_order_ids)) {
                return;
            }

            // Order with relationship data get
            if (!is_array($last_order_ids)) {
                $order_ids_convert_to_array = collect($last_order_ids)->toArray();
            } else {
                $order_ids_convert_to_array = $last_order_ids;
            }

            $orders = Order::with('customer', 'orderAddress','deliveryman')
                ->whereIn('id' ,$order_ids_convert_to_array)
                ->get();

            // if order not found
            if ($orders->count() === 0) {
                return;
            }

            // check others data
            $other_check = false;
            if (!empty($otherCheckData) && $otherCheckData == 'new-order'){
                $other_check = true;
            }

            foreach ($orders as $order_details) {
                $last_order_id = $order_details->id;
                // Notification Data
                $messages = getOrderStatusMessage($order_details, $other_check);
                $data = ['order_id' => $order_details->id];

                // create notification for every one
                $this->notifyAdmin($messages['title'], $messages['admin'], $data);
                $this->notifyCustomer($order_details, $messages['title'], $messages['customer'], $data);
                $this->notifyDeliveryman($order_details, $messages['title'], $messages['deliveryman'], $data);

                // admin change order status
                if ($otherCheckData === 'admin_order_status_cpps' || $otherCheckData === 'admin_order_status_delivery') {
                    // Customer notification
                    $this->sendOrderNotification(
                        $order_details->customer,
                        'customer_id',
                        $order_details->customer_id ?? 0,
                        $last_order_id,
                        $messages['title'],
                        $messages['customer']
                    );

                    // Deliveryman notification
                    $this->sendOrderNotification(
                        $order_details->deliveryman,
                        'deliveryman_id',
                        $order_details->deliveryman?->id ?? 0,
                        $last_order_id,
                        $messages['title'],
                        $messages['deliveryman']
                    );

                }elseif($otherCheckData === 'admin_order_status_cancelled' || $otherCheckData === 'branch_order_cancelled'){
                    // Customer notification
                    $this->sendOrderNotification(
                        $order_details->customer,
                        'customer_id',
                        $order_details->customer_id ?? 0,
                        $last_order_id,
                        $messages['title'],
                        $messages['customer']
                    );

                }elseif($otherCheckData === 'admin_order_assign_deliveryman'){
                    // Deliveryman notification
                    $this->sendOrderNotification(
                        $order_details->deliveryman,
                        'deliveryman_id',
                        $order_details->deliveryman?->id ?? 0,
                        $last_order_id,
                        $messages['title'] = "New Order Assigned: #{$last_order_id}",
                        $messages['deliveryman'] = "You have been assigned to deliver Order #{$last_order_id}. Please check the details and proceed with the delivery."
                    );

                }elseif($otherCheckData === 'customer_order_status_cancelled'){
                    //  Admin  notification
                    $this->sendAdminNotification(
                        $order_details,
                        $messages['title'],
                        $messages['admin']
                    );

                }elseif($otherCheckData === 'deliveryman_order_status_psd'){

                    // Customer notification
                    $this->sendOrderNotification(
                        $order_details->customer,
                        'customer_id',
                        $order_details->customer_id ?? 0,
                        $last_order_id,
                        $messages['title'],
                        $messages['customer']
                    );

                    //  Admin  notification
                    $this->sendAdminNotification(
                        $order_details,
                        $messages['title'],
                        $messages['admin']
                    );

                }else{
                    // Send admin notification only once
                    $this->sendAdminNotification(
                        $order_details,
                        $messages['title'],
                        $messages['admin']
                    );
                }
            }
        }catch (\Exception $exception){}
    }

    private function sendOrderNotification($recipient_user, $idKey, $idValue, $orderId, $title, $body)
    {
        if (empty($recipient_user)) {
            return;
        }

        $user_firebase_token = [$recipient_user->firebase_token];
        $tokens = array_filter(array_unique($user_firebase_token));

        if (!empty($tokens)) {
            $notification_data = [
                "title" => $title,
                "detailed_title" => "-",
                "order_id" => $orderId,
                $idKey => $idValue,
                "body" => $body,
                "description" => "-",
                "type" => "order",
                "sound" => "default",
                "screen" => "-"
            ];

            // Send notification
            $this->sendFirebaseNotification($tokens, $title, $body, $notification_data);
            
        }
    }


    private function sendAdminNotification(Order $order_details, string $title, string $body): void
    {

        if ($this->adminNotified) return;

        $super_admin = User::where('activity_scope', 'system_level')
            ->where('slug', 'super_admin')
            ->first();

        if (!$super_admin || empty($super_admin->firebase_token)) return;

        $tokens = is_array($super_admin->firebase_token)
            ? $super_admin->firebase_token
            : [$super_admin->firebase_token];

        $tokens = array_filter(array_unique($tokens));

        $notification_data = [
            "title" => $title,
            "detailed_title" => "-",
            "order_id" => $order_details->id,
            "body" => $body,
            "description" => "-",
            "type" => "order",
            "sound" => "default",
            "screen" => "-",
        ];

        $this->sendFirebaseNotification($tokens, $title, $body, $notification_data);

        $this->adminNotified = true;
    }


    // Notify Admins
    protected function notifyAdmin($title, $message, $data)
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('slug', 'super_admin');
        })->first();

        if ($admin) {
            $this->sendNotification($admin->id, 'admin', $title, $message, $data);
        }
    }

    // Notify Customer
    protected function notifyCustomer($order_details, $title, $message, $data)
    {
        if ($order_details->customer && $order_details->customer) {
            $this->sendNotification($order_details->customer->id, 'customer', $title, $message, $data);
        }
    }

    // Notify Deliveryman
    protected function notifyDeliveryman($order_details, $title, $message, $data)
    {
        if ($order_details->deliveryman && !empty($order_details->deliveryman)) {
            $this->sendNotification($order_details->deliveryman->id, 'deliveryman', $title, $message, $data);
        }
    }

    // Send and  Notification
    protected function sendNotification($user_id, $notifiable_type, $title, $message, $data)
    {
        // Store notification in database
        UniversalNotification::create([
            'notifiable_id'  => $user_id,
            'title'          => $title,
            'message'        => $message,
            'data'           => json_encode($data),
            'notifiable_type' => $notifiable_type,
            'status'         => 'unread',
        ]);
    }

    public function sendFirebaseNotification(array $firebaseTokens, $title, $body, $data)
    {

        try {
            // Check if the third parameter (image URL) is being passed as an array.
            $imageUrl = isset($data['imageUrl']) && is_string($data['imageUrl']) ? $data['imageUrl'] : null;
            // Path to the Firebase credentials JSON file
            $credentialsPath = storage_path('app/firebase/firebase.json');
            // Load the credentials from the JSON file
            $jsonCredentials = file_get_contents($credentialsPath);
            $credentials = json_decode($jsonCredentials, true);
            // Convert to JSON
            $jsonCredentials = json_encode($credentials);
            // Initialize Firebase Admin SDK
            $factory = (new Factory)->withServiceAccount($jsonCredentials);
            $messaging = $factory->createMessaging();
            // Create the Notification object
            $notification = Notification::create($title, $body, $imageUrl);
            // Process the data to ensure all values are scalar
            $processedData = [];
            foreach ($data as $key => $value) {
                // Convert array values to JSON, otherwise cast to string
                if (is_array($value)) {
                    // Ensure only top-level arrays are converted to JSON
                    $processedData[$key] = json_encode($value); // Convert array to JSON string
                } else {
                    $processedData[$key] = (string)$value; // Ensure it's a string
                }
            }

            // for mobile app  Prepare the data array without nested arrays
            $dataToSend = array_merge(
                [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ],
                $processedData
            );

            // for web Prepare the data array without nested arrays
            $webPushConfig = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'icon' => '/logo.png',
                    'click_action' => $data['click_action'] ?? '',
                ],
            ]);

            $firebaseTokens = array_unique($firebaseTokens);

            // Construct the CloudMessage with notification and data payloads
            $message = CloudMessage::new()
                ->withNotification($notification)  // Pass the Notification object
                ->withData($dataToSend)
                ->withWebPushConfig($webPushConfig); // Required for web

            // Send the notification to multiple tokens
            $messaging->sendMulticast($message, $firebaseTokens);


        }catch (\Exception $exception){}
    }


}
