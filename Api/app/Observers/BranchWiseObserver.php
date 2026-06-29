<?php

namespace App\Observers;

use Modules\Branch\app\Models\Branch;
use Modules\Chat\app\Models\Chat;

class BranchWiseObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function created(Branch $store): void
    {

        // Create live chat data
        if (moduleExists('Chat')) {
            Chat::create([
                'user_id' => $store->id,
                'user_type' => 'store',
            ]);
        }
    }

}
