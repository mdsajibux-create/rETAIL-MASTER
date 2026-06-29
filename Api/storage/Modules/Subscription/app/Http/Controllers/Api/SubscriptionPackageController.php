<?php

namespace Modules\Subscription\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Modules\Subscription\app\Models\Subscription;
use Modules\Subscription\app\Models\SubscriptionHistory;
use Modules\Subscription\app\Transformers\SubscriptionPackagePublicResource;

class SubscriptionPackageController extends Controller
{
    public function packages(Request $request)
    {
        $claimedTrial = SubscriptionHistory::where('store_id', $request->store_id)
            ->where('price', 0)
            ->where('payment_status', 'paid')
            ->first();

        $query = Subscription::where('status', 1);

        // Exclude the claimed trial package if it exists
        if ($claimedTrial) {
            $query->where('id', '!=', $claimedTrial->subscription_id);
        }

        $packages = $query->paginate(10);

        return response()->json([
            'packages' => SubscriptionPackagePublicResource::collection($packages),
            'meta' => new PaginationResource($packages),
        ]);
    }
}
