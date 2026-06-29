<?php

namespace App\Enums;

enum Role: string
{
    case SUPER_ADMIN = 'system_level';
    case STORE_OWNER = 'branch_level';
    case CUSTOMER = 'customer_level';
    case DELIVERY_MAN = 'delivery_level';
    case FITTER_MAN = 'fitter_level';
}
