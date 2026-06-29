<?php

namespace App\Enums;

enum ProductType: string
{
    case FURNITURE = 'furniture';
    case FLOWER  = 'flower';


    // Return human-readable label
    public function label(): string
    {
        return match($this) {
            self::FURNITURE => 'Furniture',
            self::FLOWER    => 'Flower'
        };
    }

    // Helper: get all values
    public static function values(): array
    {
        return array_map(fn($type) => $type->value, self::cases());
    }

    // Helper: get all labels
    public static function labels(): array
    {
        return array_map(fn($type) => $type->label(), self::cases());
    }

    public static function dropdown(): array
    {
        return array_map(fn($type) => [
            'label' => $type->label(),
            'value' => $type->value,
        ], self::cases());
    }

    public static function images(): array
    {
        return [
            self::FURNITURE->value => '',
            self::FLOWER->value => '',
        ];
    }

    public function image(): string
    {
        return self::images()[$this->value] ?? '';
    }
}

