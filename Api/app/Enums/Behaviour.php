<?php

namespace App\Enums;

enum Behaviour: string
{
    case CONSUMABLE = 'consumable';
    case SERVICE = 'service';
    case DIGITAL = 'digital';
    case COMBO = 'combo';
    case PHYSICAL = 'physical';

}
