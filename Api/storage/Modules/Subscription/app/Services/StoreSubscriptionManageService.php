<?php

namespace Modules\Subscription\app\Services;

use Modules\Subscription\app\Models\StoreSubscription;

class StoreSubscriptionManageService
{
    public function storeSubscriptionInfo($storeId)
    {

        $store_subscription = StoreSubscription::where('store_id', $storeId)->first();

        if (!$store_subscription) {
            return false;
        }

        return $store_subscription;
    }
}
