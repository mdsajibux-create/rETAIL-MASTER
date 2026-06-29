<?php

namespace App\Enums;

enum FuelType: string
{
    case PETROL = 'petrol';
    case DIESEL = 'diesel';
    case ELECTRIC = 'electric';
    case HYBRID = 'hybrid';
}
