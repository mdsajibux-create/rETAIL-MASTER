<?php

namespace App\Enums;

enum StatusType: string
{
    case ACTIVE = 'active';
    case DISPATCHED = 'dispatched';
    case RECEIVED   = 'received';
    case DRAFT              = 'draft';
    case PENDING            = 'pending';
    case APPROVED           = 'approved';
    case INACTIVE           = 'inactive';
    case SUSPENDED          = 'suspended';
    case IN_TRANSIT         = 'in_transit';        // = dispatched
    case PARTIALLY_RECEIVED = 'partially_received';
    case COMPLETED          = 'completed';
    case REJECTED           = 'rejected';
    case CANCELLED          = 'cancelled';
}
