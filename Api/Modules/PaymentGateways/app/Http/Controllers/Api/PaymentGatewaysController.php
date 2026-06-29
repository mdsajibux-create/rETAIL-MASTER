<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Support\Facades\Cache;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Transformers\PaymentGatewaysListPublicResource;
use Modules\SystemCore\app\Models\SettingOption;

class PaymentGatewaysController extends Controller
{
    public function currencySettingsGet(){

        $keys = [
            'com_site_global_currency',
            'com_site_currency_symbol_position',
            'com_site_comma_form_adjustment_amount',
            'com_site_enable_disable_decimal_point',
            'com_site_space_between_amount_and_symbol',
        ];

        $currencies = Cache::remember('currency_settings_public', 86400, function () use ($keys) {
            return SettingOption::whereIn('option_name', $keys)
                ->pluck('option_value', 'option_name')
                ->toArray();
        });

        return response()->json([
            'currencies_info' => [
                'com_site_global_currency' => $currencies['com_site_global_currency'] ?? null,
                'com_site_currency_symbol_position' => $currencies['com_site_currency_symbol_position'] ?? null,
                'com_site_comma_form_adjustment_amount' => $currencies['com_site_comma_form_adjustment_amount'] ?? null,
                'com_site_enable_disable_decimal_point' => $currencies['com_site_enable_disable_decimal_point'] ?? null,
                'com_site_space_between_amount_and_symbol' => $currencies['com_site_space_between_amount_and_symbol'] ?? null,
            ],
        ]);
    }

    public function paymentGateways(){
        $paymentGateways = PaymentGateway::where('status', 1)->get();
        return response()->json([
            'paymentGateways' => PaymentGatewaysListPublicResource::collection($paymentGateways),
        ]);
    }

}
