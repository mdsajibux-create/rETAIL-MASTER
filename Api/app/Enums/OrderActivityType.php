<?php

namespace App\Enums;

enum OrderActivityType: string
{
    case CASH_COLLECTION = 'cash_collection';
    case CASH_DEPOSIT = 'cash_deposit';

}
