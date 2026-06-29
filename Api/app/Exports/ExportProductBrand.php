<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\Catalog\app\Models\ProductBrand;

class ExportProductBrand implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return ProductBrand::select('id',)->get();
    }
}
