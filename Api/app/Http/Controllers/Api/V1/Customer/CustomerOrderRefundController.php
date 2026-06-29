<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Interfaces\OrderRefundInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerOrderRefundController extends Controller
{
    public function __construct(protected OrderRefundInterface $orderRefundRepo)
    {

    }

    public function orderRefundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
            'order_refund_reason_id' => 'required|integer|exists:order_refund_reasons,id',
            'customer_note' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:jpg,png,jpeg,webp,zip,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Prepare data array
        $data = $request->only(['order_id', 'order_refund_reason_id', 'customer_note']);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            // Generate a unique filename
            $filename = 'uploads/order-refund/' . now()->timestamp . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            // Save the file to private storage
            Storage::disk('import')->put($filename, file_get_contents($file->getRealPath()));
            // Add the file path to the data array
            $data['file'] = $filename;
        }

        // Call repository method with properly structured data
        $success = $this->orderRefundRepo->create_order_refund_request($data['order_id'], $data);

        return match ($success) {
            'does_not_belong_to_customer' => response()->json([
                'message' => __('messages.order_does_not_belong_to_customer'),
            ], 422),

            'not_delivered' => response()->json([
                'message' => __('messages.order_is_not_delivered'),
            ], 422),

            'already_requested_for_refund' => response()->json([
                'message' => __('messages.order_already_request_for_refund'),
            ], 422),

            true => response()->json([
                'message' => __('messages.order_refund_request_success'),
            ], 200),

            default => response()->json([
                'message' => __('messages.order_refund_request_failed'),
            ], 500),
        };

    }

}
