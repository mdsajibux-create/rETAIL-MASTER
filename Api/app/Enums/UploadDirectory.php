<?php

namespace App\Enums;

enum UploadDirectory: string
{
    case BRAND = 'brand';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
    case STORE = 'store';
    case GENERAL = 'general';
}