<?php

namespace App\Exports;

use App\Http\Resources\Admin\AdminOrderReportResource;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $orderDetails;

    public function __construct($orderDetails)
    {
        $this->orderDetails = $orderDetails;
    }
    public function collection()
    {
        return AdminOrderReportResource::collection($this->orderDetails);
    }
    public function headings(): array
    {
        return [
            'ID',
            'Order ID',
            'Invoice',
            'Area',
            'Customer',
            'Payment Gateway',
            'Payment Status',
            'Order Amount',
            'Coupon Discount (Admin)',
            'Product Discount',
            'Flash Discount (Admin)',
            'Shipping Charge',
            'Additional Charge',
            'Refund Status',
            'Status',
            'Base Price',
            'Price',
            'Quantity',
            'Line Total Price (with qty)',
            'Line Total Excluding Tax',
            'Tax Rate',
            'Tax Amount',
            'Total Tax Amount',
            'Line Total Price',
        ];
    }
}
