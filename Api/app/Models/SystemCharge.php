<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemCharge extends Model
{
    protected $table = 'system_charges';

    protected $fillable = [
        'zone_system_enable',
        'order_tax',
        'order_shipping_charge',                // Shipping charge for orders
        'order_confirmation_by',                // Manual or automatic confirmation
        'order_include_tax_amount',                          // Include tax in order calculations
        'order_additional_charge_enable_disable',            // Enable or disable additional charge
        'order_additional_charge_name',               // Name of the additional charge
        'order_additional_charge_amount',             // Amount of the additional charge
        'deliveryman_earning_type',             // earning type
        'deliveryman_commission_type',             // commission type
        'deliveryman_commission_value',             // commission value
    ];

    protected $casts = [
        'zone_system_enable' => 'boolean',
        'order_include_tax_amount' => 'boolean',
        'order_additional_charge_enable_disable' => 'boolean',
    ];

    public function calculateAdditionalCharge(float $amount): float
    {
        if (!$this->order_additional_charge_enable_disable) {
            return 0;
        }

        return $this->order_additional_charge_amount;
    }
}
