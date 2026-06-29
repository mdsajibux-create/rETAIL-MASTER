<?php

namespace App\Enums;

enum BranchType: string
{
    case WAREHOUSE = 'warehouse';
    case RETAIL    = 'retail';
    case OUTLET    = 'outlet';
    case HUB       = 'hub';
    case DARK_STORE = 'dark_store';
}

