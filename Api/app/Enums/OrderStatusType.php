<?php

namespace App\Enums;

enum OrderStatusType: string
{
    case PENDING = 'pending';
    case ACTIVE = 'confirmed';
    case PROCESSING = 'processing';
    case PICKUP = 'pickup';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';
}
