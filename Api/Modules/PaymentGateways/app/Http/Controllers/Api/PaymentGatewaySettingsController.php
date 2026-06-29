<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminCurrencyDetailsResource;
use App\Http\Resources\Admin\AdminCurrencyResource;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Modules\PaymentGateways\app\Models\Currency;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Transformers\PaymentGatewaysResource;

class PaymentGatewaySettingsController extends Controller
{
    public function paymentGatewayList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|exists:payment_gateways,slug',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // if get payment gateway info
        $gateway_name = $request->name;
        $query = PaymentGateway::query();

        if (!empty($gateway_name)){
            $paymentGateway =  $query->where('name', $gateway_name)->first();
            return response()->json([
                'status' => true,
                'gateways' => new PaymentGatewaysResource($paymentGateway)
            ],200);
        }

        $paymentGateway =  $query->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => true,
            'gateways' => PaymentGatewaysResource::collection($paymentGateway),
            'meta' => new PaginationResource($paymentGateway)
        ],200);
    }

    public function paymentGatewayStatusUpdate(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|exists:payment_gateways,slug',
            'status' => 'nullable|boolean|required_without:is_test_mode',
            'is_test_mode' => 'nullable|boolean|required_without:status',
            'image' => 'nullable',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get validated data
        $validatedData = $validator->validated();
        $gateway = PaymentGateway::where('slug', $validatedData['name'])->first();

        if (!$gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid gateway name.'
            ], 400);
        }

        // others payment gateway update
        $gateway->update([
            'status' => $request->get('status', $gateway->status),
            'is_test_mode' => $request->get('is_test_mode', $gateway->is_test_mode),
            'image' => $request->get('image', $gateway->image),
        ]);

        Artisan::call('optimize:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'Payment gateway status updated successfully.'
        ]);


    }

    public function paymentGatewayUpdate(Request $request, $gateway = null)
    {
        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|exists:payment_gateways,slug',
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
            $gateway = PaymentGateway::where('slug', $validatedData['name'])->first();

            if (!$gateway) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid gateway name.'
                ], 400);
            }

            // if cash on delivery update
            if ($gateway->slug === 'cash_on_delivery') {
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
            } else {
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


        // if get payment gateway info
        $gateway_name = $gateway;
        if (!empty($gateway_name)){
            $paymentGateway = PaymentGateway::where('slug', $gateway_name)->first();

            // If demo mode is enabled
            if (Config::get('demoMode.check')) {
                $authCredentialsJson = $paymentGateway->auth_credentials;
                $authCredentials = json_decode($authCredentialsJson, true);
                if (is_array($authCredentials)) {
                    foreach ($authCredentials as $key => $value) {
                        if ($value) {
                            $authCredentials[$key] = '';
                        }
                    }
                    // Update the attribute with cleaned JSON
                    $paymentGateway->auth_credentials = json_encode($authCredentials);
                }
            }

            return response()->json([
                'status' => 'success',
                'gateways' => new PaymentGatewaysResource($paymentGateway)
            ]);

        }else{
            $paymentGateway = PaymentGateway::all();

            return response()->json([
                'status' => 'success',
                'gateways' => PaymentGatewaysResource::collection($paymentGateway)
            ]);
        }


    }

    public function currencySettingsUpdate(Request $request)
    {
            $validator = Validator::make($request->all(), [
                'com_site_space_between_amount_and_symbol' => 'nullable|string',
                'com_site_enable_disable_decimal_point' => 'nullable|string',
                'com_site_comma_form_adjustment_amount' => 'nullable|string',
                'com_site_currency_symbol_position' => 'nullable|string',
                'com_site_global_currency' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fields = [
                'com_site_space_between_amount_and_symbol',
                'com_site_enable_disable_decimal_point',
                'com_site_comma_form_adjustment_amount',
                'com_site_currency_symbol_position',
                'com_site_global_currency',
            ];

            foreach ($fields as $field) {
                $value = $request->input($field) ?? null;
                com_option_update($field, $value);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Currency settings updated successfully.',
            ],200);
    }


    public function currencySettingsGet()
    {
        $currencySettings = [
            'com_site_currency_symbol_position' => com_option_get('com_site_currency_symbol_position'),
            'com_site_global_currency' => com_option_get('com_site_global_currency'),
            'com_site_comma_form_adjustment_amount' => com_option_get('com_site_comma_form_adjustment_amount'),
            'com_site_enable_disable_decimal_point' => com_option_get('com_site_enable_disable_decimal_point'),
            'com_site_space_between_amount_and_symbol' => com_option_get('com_site_space_between_amount_and_symbol'),
        ];

        return response()->json([
            'status' => 'success',
            'currency_settings' => $currencySettings,
        ]);

    }


    public function listCurrency(Request $request)
    {
        $currencies = Currency::with('translations')
            ->latest()
            ->paginate($per_page ?? 10);
        return response()->json([
            'data' => AdminCurrencyResource::collection($currencies),
            'meta' => new PaginationResource($currencies),
        ]);
    }

    public function createCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('is_default') && $request->is_default) {
            Currency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency = Currency::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'symbol' => $request->symbol,
            'exchange_rate' => $request->exchange_rate,
            'is_default' => $request->is_default ?? false,
            'status' => $request->status ?? true,
        ]);
        createOrUpdateTranslation($request, $currency->id, Currency::class, $currency->translationKeys);

        return response()->json([
            'message' => 'Currency created successfully',
            'data' => $currency
        ], 201);
    }


    public function getCurrencyById(Request $request, $id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'message' => 'Currency not found'
            ], 404);
        }

        return response()->json([
            'message' => 'Currency retrieved successfully',
            'data' => new AdminCurrencyDetailsResource($currency)
        ]);
    }


    public function updateCurrency(Request $request)
    {
        $id = $request->id;
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'message' => 'Currency not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:currencies,code,' . $id,
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0',
            'is_default' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('is_default') && $request->is_default) {
            Currency::where('is_default', true)->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $currency->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'symbol' => $request->symbol,
            'exchange_rate' => $request->exchange_rate,
            'is_default' => $request->is_default ?? $currency->is_default,
            'status' => $request->status ?? $currency->status,
        ]);
        createOrUpdateTranslation($request, $currency->id, Currency::class, $currency->translationKeys);

        return response()->json([
            'message' => 'Currency updated successfully',
            'data' => $currency
        ]);
    }


    public function deleteCurrency($id)
    {
        $currency = Currency::find($id);

        if (!$currency) {
            return response()->json([
                'message' => 'Currency not found'
            ], 404);
        }

        if ($currency->is_default) {
            return response()->json([
                'message' => 'Cannot delete the default currency'
            ], 422);
        }

        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully'
        ]);
    }
}
