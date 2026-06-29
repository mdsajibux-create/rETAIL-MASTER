<?php

namespace App\Observers;

use App\Models\Customer;
use Modules\Chat\app\Models\Chat;
use Modules\Wallet\app\Models\Wallet;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // Create a wallet for the customer with initial balance
        Wallet::create([
            'owner_id' => $customer->id,
            'owner_type' => Customer::class,  // Set the polymorphic type
            'balance' => 0,  // Set initial balance
            'status' => 1,   // Set the wallet as active
        ]);

        // Create live chat data
        if (moduleExists('Chat')) {
            Chat::create([
                'user_id' => $customer->id,
                'user_type' => 'customer',
            ]);
        }

    }

    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        //
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        //
    }
}
