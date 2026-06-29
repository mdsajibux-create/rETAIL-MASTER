<?php

namespace Modules\Customer\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\SubscribeRequest;
use App\Http\Resources\SubscribeResource;
use App\Interfaces\SubscriberInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscriberManageController extends Controller
{
    public function __construct(protected SubscriberInterface $subscriberRepo)
    {

    }
    public function subscribe(SubscribeRequest $request)
    {
        $subscriber = $this->subscriberRepo->subscribe($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'You have successfully subscribed!',
            'subscriber' => $subscriber,
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->input('email');
        $subscriber = $this->subscriberRepo->unsubscribe($email);

        if ($subscriber) {
            return response()->json([
                'success' => true,
                'message' => 'You have successfully unsubscribed.',
                'subscriber' => $subscriber,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Subscriber not found or already unsubscribed.',
        ], 404);
    }

    public function listSubscribers(Request $request)
    {
        $filters = $request->only(['status', 'email', 'subscribed_at', 'sortOrder', 'perPage']);
        $subscribers = $this->subscriberRepo->getSubscribers($filters);
        return SubscribeResource::collection($subscribers);
    }

    public function bulkSubscriberStatusChange(Request $request)
    {
        // Create validator instance
        $validator = Validator::make($request->all(), ([
            'ids' => 'required|array',
            'status' => 'required|boolean',
        ]));

        // Check if validation fails
        if ($validator->fails()) {
            // Handle validation errors
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $success = $this->subscriberRepo->changeStatus($request->all());
        if ($success) {
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'message' => 'Bulk status updated successfully!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Bulk status updated failed!',
            ]);
        }

    }

    public function sendBulkEmail(Request $request)
    {
        // Create validator instance
        $validator = Validator::make($request->all(), ([
            'ids' => 'required|array',
        ]));
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $success = $this->subscriberRepo->sendBulkMail($request->all());
        if ($success) {
            return response()->json([
                'success' => true,
                'status_code' => 200,
                'message' => 'Bulk email sent successfully!',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'status_code' => 500,
                'message' => 'Bulk email sent failed!',
            ]);
        }
    }
    public function deleteSubscriber($id)
    {
        $this->subscriberRepo->delete($id);
        return $this->success(translate('messages.delete_success',['name'=>'Subscriber']));
    }
}
