<?php

namespace App\Enums;

enum WalletOwnerType: string
{
    case STORE = 'Modules\Branch\app\Models\Branch';
    case DELIVERYMAN = 'App\Models\User';
    case CUSTOMER = 'App\Models\Customer';
}