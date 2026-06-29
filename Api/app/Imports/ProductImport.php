<?php

namespace App\Imports;

use App\Enums\Behaviour;
use App\Enums\ProductType;
use App\Enums\StatusType;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Modules\Product\app\Models\Product;

class ProductImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    public function chunkSize(): int
    {
        return 1000;
    }

    public function collection(\Illuminate\Support\Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $productId = $row['id'];
            $slug = $row['slug'];

            $productExists = Product::where('id', $productId)
                ->orWhere('slug', $slug)
                ->exists();

            if ($productExists) {
                continue;
            }

            // Save or update product to the database
            Product::create(
                [
                    "category_id" => $row['category_id'],
                    "unit_id" => $row['unit_id'] ?? null,
                    "name" => $row['name'],
                    "slug" => $row['slug'] ?? 'no-slug',
                    "warranty" => $row['warranty'],
                    "return_in_days" => $row['return_in_days'],
                    "type" => $row['type'],
                    "cash_on_delivery" => $row['cash_on_delivery'],
                    "behaviour" => $row['behaviour'],
                    "delivery_time_min" => $row['delivery_time_min'],
                    "delivery_time_max" => $row['delivery_time_max'],
                    "max_cart_qty" => $row['max_cart_qty'],
                    "order_count" => $row['order_count'] ?? 0,
                    "views" => $row['views'] ?? 0,
                    "status" => $row['status'],
                    "available_time_starts" => $row['available_time_starts'],
                    "available_time_ends" => $row['available_time_ends'],
                ]
            );
        }
    }

    /**
     * Define validation rules for imported rows
     */
    public function rules(): array
    {
        return [
            "category_id" => "nullable",
            "brand_id" => "nullable",
            "unit_id" => "nullable",
            "type" => "required|in:" . implode(',', array_column(ProductType::cases(), 'value')),
            "name" => "required",
            "behaviour" => "nullable|in:" . implode(',', array_column(Behaviour::cases(), 'value')),
            "status" => "required|in:" . implode(',', array_column(StatusType::cases(), 'value')),
        ];
    }

    /**
     * Define custom error messages
     */
    public function customValidationMessages(): array
    {
        return [
            "type.required" => "The type is required.",
            "status.required" => "The status is required.",
        ];
    }
}
