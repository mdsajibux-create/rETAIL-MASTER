<?php

namespace Modules\Subscription\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Subscription\app\Models\StoreSubscription;
use Modules\Subscription\app\Models\SubscriptionHistory;
use Modules\Subscription\app\Services\SubscriptionService;
use Modules\Subscription\app\Transformers\StoreSubscriptionHistoryResource;

class AdminSubscriptionSellerController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index(Request $request)
    {
        $query = StoreSubscription::query();

        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhereHas('store', function ($storeQuery) use ($request) {
                        $storeQuery->where('name', 'LIKE', '%' . $request->search . '%');
                    });
            });
        }

        if (isset($request->status)) {
            $query->where('status', $request->status ?? 1);
        }

        if ($request->has('start_date') && !empty($request->start_date) && $request->has('end_date') && !empty($request->end_date)) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->has('start_expire_date') && !empty($request->start_expire_date) && $request->has('end_expire_date') && !empty($request->end_expire_date)) {
            $query->whereBetween('expire_date', [$request->start_expire_date, $request->end_expire_date]);
        }

        if (isset($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        $subscriptions = $query->paginate($request->per_page ?? 10);
        return response()->json([
            'success' => true,
            'data' => StoreSubscriptionHistoryResource::collection($subscriptions),
            'meta' => new PaginationResource($subscriptions),
        ]);
    }

    public function subscriptionHistory(Request $request)
    {
        $store_subscription_id = $request->id;
        $subscription_history = SubscriptionHistory::where('store_subscription_id', $store_subscription_id)
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => StoreSubscriptionHistoryResource::collection($subscription_history),
            'meta' => new PaginationResource($subscription_history),
        ]);
    }

    public function assignStoreSubscription(Request $request)
    {
        $result = $this->subscriptionService->adminAssignStoreSubscription($request->all());
        return response()->json($result, $result['status_code'] ?? 200);
    }

    public function statusChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:store_subscriptions,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $storeSubscription = StoreSubscription::findOrFail($request->id);
        if (!$storeSubscription) {
            return response()->json([
                'success' => false,
                'message' => 'Store Subscription not found',
            ], 404);
        }

        $storeSubscription->update(['status' => $storeSubscription->status == 0 ? 1 : 0]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully',
        ], 200);
    }

}
