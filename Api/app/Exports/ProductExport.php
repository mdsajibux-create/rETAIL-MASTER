<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Branch\app\Models\Branch;
use Modules\Product\app\Models\Product;

class ProductExport implements FromCollection, WithHeadings
{
    protected $shopIds;
    protected $productIds;
    protected $startDate;
    protected $endDate;
    protected $minId;
    protected $maxId;
    protected $exportWithoutData;
    protected $defaultColumns = [
        "id",
        "category_id",
        "brand_id",
        "unit_id",
        "name",
        "slug",
        "warranty",
        "return_in_days",
        "type",
        "cash_on_delivery",
        "behaviour",
        "delivery_time_min",
        "delivery_time_max",
        "max_cart_qty",
        "order_count",
        "views",
        "status",
        "available_time_starts",
        "available_time_ends",
        "manufacture_date",
        "expiry_date",
    ];

    /**
     * Constructor to accept selected shop IDs, product IDs, date range, and ID range
     */
    public function __construct(
        array $shopIds = [],
        array $productIds = [],
              $startDate = null,
              $endDate = null,
              $minId = null,
              $maxId = null,
              $exportWithoutData = false
    )
    {
        $this->shopIds = $shopIds;
        $this->productIds = $productIds;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->minId = $minId;
        $this->maxId = $maxId;
        $this->exportWithoutData = $exportWithoutData;
    }

    /**
     * Get the collection of products based on provided filters
     */
    public function collection()
    {
        if ($this->exportWithoutData) {
            // Return an empty array if export_without_data is true
            return collect([]);
        }
        // Start building the query
        $query = Product::query();

        // Apply product filter if provided
        if (!empty($this->productIds)) {
            $query->whereIn('id', $this->productIds);
        }

        // Apply date range filter if provided
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)]);
        }

        // Apply ID range filter if provided
        if ($this->minId && $this->maxId) {
            $query->whereBetween('id', [$this->minId, $this->maxId]);
        }

        // Select required columns
        $query->select(
            "id",
            "category_id",
            "brand_id",
            "unit_id",
            "name",
            "slug",
            "warranty",
            "return_in_days",
            "type",
            "cash_on_delivery",
            "behaviour",
            "delivery_time_min",
            "delivery_time_max",
            "max_cart_qty",
            "order_count",
            "views",
            "status",
            "available_time_starts",
            "available_time_ends",
            "manufacture_date",
            "expiry_date"
        );

        // Execute the query and return the collection
        return $query->get();
    }

    /**
     * Define column headings for the Excel file
     */
    public function headings(): array
    {
        return $this->defaultColumns;
    }
}
