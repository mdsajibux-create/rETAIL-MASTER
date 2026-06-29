<?php

namespace Modules\Pos\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;

class PosCustomer extends Model
{
    protected $fillable = [
        'customer_id',
        'branch_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

}
