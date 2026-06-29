<?php

namespace Modules\PaymentGateways\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Transformers\PaymentGatewaysListPublicResource;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Currency;


class PaymentGatewaysController extends Controller
{
    public function currencySettingsGet(){
        $currencies = [
            'com_site_global_currency' => com_option_get('com_site_global_currency'),
            'com_site_currency_symbol_position' => com_option_get('com_site_currency_symbol_position'),
            'com_site_default_currency_to_usd_exchange_rate' => com_option_get('com_site_default_currency_to_usd_exchange_rate'),
            'com_site_comma_form_adjustment_amount' => com_option_get('com_site_comma_form_adjustment_amount'),
            'com_site_enable_disable_decimal_point' => com_option_get('com_site_enable_disable_decimal_point'),
            'com_site_space_between_amount_and_symbol' => com_option_get('com_site_space_between_amount_and_symbol'),
        ];
        return response()->json([
            'currencies_info' => $currencies,
        ]);
    }

    public function paymentGateways(){
        $paymentGateways = PaymentGateway::where('status', 1)->get();
        return response()->json([
            'paymentGateways' => PaymentGatewaysListPublicResource::collection($paymentGateways),
        ]);
    }

}
