<?php

namespace Modules\PaymentGateways\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Transformers\PaymentGatewaysListPublicResource;
use Modules\PaymentGateways\app\Transformers\PaymentGatewaysResource;

class PaymentGatewaySettingsController extends Controller
{
    public function settingsGetAndUpdate(Request $request, $gateway)
    {

        // update payment gateway  and currency settings
        if ($request->isMethod('POST')) {

            if (!empty($request->currency_settings) && $request->currency_settings === 'update') {
                $validator = Validator::make($request->all(), [
                    'com_site_space_between_amount_and_symbol' => 'nullable|string',
                    'com_site_enable_disable_decimal_point' => 'nullable|string',
                    'com_site_comma_form_adjustment_amount' => 'nullable|string',
                    'com_site_default_currency_to_usd_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_myr_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_brl_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_zar_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_ngn_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_inr_exchange_rate' => 'nullable|string',
                    'com_site_default_currency_to_idr_exchange_rate' => 'nullable|string',
                    'com_site_default_payment_gateway' => 'nullable|string',
                    'com_site_currency_symbol_position' => 'nullable|string',
                    'com_site_euro_to_ngn_exchange_rate' => 'nullable|string',
                    'com_site_usd_to_ngn_exchange_rate' => 'nullable|string',
                    'com_site_manual_payment_description' => 'nullable|string',
                    'com_site_manual_payment_name' => 'nullable|string',
                    'com_site_payment_gateway' => 'nullable|string',
                    'com_site_global_currency' => 'nullable|string',
                    'com_site_comma_form_adjustment_amount',
                    'com_site_enable_disable_decimal_point',
                    'com_site_space_between_amount_and_symbol',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $fields = [
                    // Currency-related settings
                    'com_site_space_between_amount_and_symbol',
                    'com_site_enable_disable_decimal_point',
                    'com_site_comma_form_adjustment_amount',
                    'com_site_default_currency_to_usd_exchange_rate',
                    'com_site_default_currency_to_myr_exchange_rate',
                    'com_site_default_currency_to_brl_exchange_rate',
                    'com_site_default_currency_to_zar_exchange_rate',
                    'com_site_default_currency_to_ngn_exchange_rate',
                    'com_site_default_currency_to_inr_exchange_rate',
                    'com_site_default_currency_to_idr_exchange_rate',
                    'com_site_euro_to_ngn_exchange_rate',
                    'com_site_usd_to_ngn_exchange_rate',
                    // Payment-related settings
                    'com_site_default_payment_gateway',
                    'com_site_manual_payment_description',
                    'com_site_manual_payment_name',
                    'com_site_payment_gateway',
                    'com_site_currency_symbol_position',
                    // Global site settings
                    'com_site_global_currency',
                    'com_site_comma_form_adjustment_amount',
                    'com_site_enable_disable_decimal_point',
                    'com_site_space_between_amount_and_symbol',
                ];
                foreach ($fields as $field) {
                    $value = $request->input($field) ?? null;
                    com_option_update($field, $value);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Currency settings updated successfully.',
                ]);
            }


            // Perform validation directly on the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|exists:payment_gateways,slug',
                'image' => 'nullable|string',
                'description' => 'nullable|string',
                'auth_credentials' => 'nullable|array',
                'status' => 'required|boolean',
                'is_test_mode' => 'required|boolean',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get validated data
            $validatedData = $validator->validated();

            // Proceed with business logic using $validatedData
            $gateway = PaymentGateway::where('slug', $validatedData['name'])->first();

            if (!$gateway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid gateway name.'
                ], 400);
            }

            // if cash on delivery update
            if($gateway->slug === 'cash_on_delivery') {
                $gateway->update([
                    'image' => $request->get('image', $gateway->image),
                    'description' => $request->get('description', $gateway->description),
                    'status' => $request->get('status', $gateway->status),
                    'is_test_mode' => $request->get('is_test_mode', $gateway->is_test_mode),
                ]);
                Artisan::call('optimize:clear');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment gateway updated successfully.'
                ]);
            }else{
                // others payment gateway update
                $auth_credentials = $request->get('auth_credentials', []);
                $gateway->update([
                    'image' => $request->get('image', $gateway->image),
                    'description' => $request->get('description', $gateway->description),
                    'status' => $request->get('status', $gateway->status),
                    'is_test_mode' => $request->get('is_test_mode', $gateway->is_test_mode),
                    'auth_credentials' => json_encode($auth_credentials),
                ]);
                Artisan::call('optimize:clear');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment gateway updated successfully.'
                ]);
            }

        }


        // if get current settings
        if (!empty($request->currency_settings) && $request->currency_settings == 'get') {
            // Example: Get all relevant currency settings
            $currencySettings = [
                // Currency-related settings
                'com_site_default_currency_to_usd_exchange_rate' => com_option_get('com_site_default_currency_to_usd_exchange_rate'),
                'com_site_default_currency_to_myr_exchange_rate' => com_option_get('com_site_default_currency_to_myr_exchange_rate'),
                'com_site_default_currency_to_brl_exchange_rate' => com_option_get('com_site_default_currency_to_brl_exchange_rate'),
                'com_site_default_currency_to_zar_exchange_rate' => com_option_get('com_site_default_currency_to_zar_exchange_rate'),
                'com_site_default_currency_to_ngn_exchange_rate' => com_option_get('com_site_default_currency_to_ngn_exchange_rate'),
                'com_site_default_currency_to_inr_exchange_rate' => com_option_get('com_site_default_currency_to_inr_exchange_rate'),
                'com_site_default_currency_to_idr_exchange_rate' => com_option_get('com_site_default_currency_to_idr_exchange_rate'),
                'com_site_euro_to_ngn_exchange_rate' => com_option_get('com_site_euro_to_ngn_exchange_rate'),
                'com_site_usd_to_ngn_exchange_rate' => com_option_get('com_site_usd_to_ngn_exchange_rate'),
                'com_site_default_payment_gateway' => com_option_get('com_site_default_payment_gateway'),
                'com_site_manual_payment_description' => com_option_get('com_site_manual_payment_description'),
                'com_site_manual_payment_name' => com_option_get('com_site_manual_payment_name'),
                'com_site_payment_gateway' => com_option_get('com_site_payment_gateway'),
                'com_site_currency_symbol_position' => com_option_get('com_site_currency_symbol_position'),
                'com_site_global_currency' => com_option_get('com_site_global_currency'),
                'com_site_comma_form_adjustment_amount' => com_option_get('com_site_comma_form_adjustment_amount'),
                'com_site_enable_disable_decimal_point' => com_option_get('com_site_enable_disable_decimal_point'),
                'com_site_space_between_amount_and_symbol' => com_option_get('com_site_space_between_amount_and_symbol'),
            ];

            // Return the current currency settings as JSON
            return response()->json([
                'status' => 'success',
                'currency_settings' => $currencySettings,
            ]);
        }

        // if get payment gateway info
        $gateway_name = $gateway;
        $paymentGateway = PaymentGateway::where('slug', $gateway_name)->first();
        if (!$paymentGateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment gateway not found.'
            ], 404);
        }


        return response()->json([
            'status' => 'success',
            'gateways' =>  new PaymentGatewaysResource($paymentGateway)
        ]);
    }

}
