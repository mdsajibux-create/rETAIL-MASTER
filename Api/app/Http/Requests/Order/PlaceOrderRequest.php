<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Models\ProductVariant;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            // Guest Checkout Validation
            'guest_info' => 'nullable|array',
            'guest_info.guest_order' => 'required_with:guest_info|boolean',

            // Apply validation only if 'guest_order' is true
            'guest_info.name' => 'required_if:guest_info.guest_order,true|string|max:255',
            'guest_info.email' => 'required_if:guest_info.guest_order,true|email|max:255|unique:customers,email',
            'guest_info.phone' => 'required_if:guest_info.guest_order,true|string|regex:/^(\+?\d{1,3})?\d{7,15}$/|unique:customers,phone',
            'guest_info.password' => 'required_if:guest_info.guest_order,true|string|min:6|max:32',
            'customer_latitude' => 'nullable',
            'customer_longitude' => 'nullable',
            // Shipping Address Validation (required only for logged-in users)
            'shipping_address_id' => 'nullable|exists:customer_addresses,id',
            // takeaway
            'name'           => 'required_if:delivery_option,takeaway,parcel|string|max:255',
            'email'          => 'required_if:delivery_option,takeaway,parcel|email|max:255',
            'contact_number'  => 'required_if:delivery_option,takeaway,parcel|string|max:255',
            'currency_code' => 'required|string|max:3',   // ISO codes are 3 letters
            'shipping_time_preferred' => 'nullable|string|max:255',
            'payment_gateway' => 'required|string|in:paypal,stripe,cash_on_delivery,razorpay,paytm,wallet,sslcommerz,bkash,nagad',
            'order_notes' => 'nullable|string|max:500',
            'order_amount' => 'nullable|decimal|min:0',
            'coupon_code' => 'nullable|string|max:50',
            'coupon_title' => 'nullable|string|max:255',
            'coupon_discount_amount_admin' => 'nullable|numeric|min:0',
            'product_discount_amount' => 'nullable|numeric|min:0',
            'flash_discount_amount_admin' => 'nullable|numeric|min:0',
            'shipping_charge' => 'nullable|numeric|min:0',
            'additional_charge_name' => 'nullable|string',
            'additional_charge_amount' => 'nullable|numeric|min:0',
            'delivery_option' => 'required|in:home_delivery,parcel,takeaway',
            'delivery_type' => 'nullable|in:standard,express,freight',
            'delivery_time' => 'nullable',
            'additional_charge' => 'nullable|numeric',
            // location
            'state_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'area_id' => 'nullable|integer',
            // items
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_campaign_id' => 'nullable|numeric',
            // product variant
            'items.*.variant_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $productId = request()->input(str_replace('.variant_id', '.product_id', $attribute));
                    $variantExists = ProductVariant::where('id', $value)->where('product_id', $productId)->exists();
                    if (!$variantExists) {
                        $fail("The selected variant does not belong to the given product.");
                    }
                }
            ],

            // discount
            'items.*.admin_discount_type' => 'nullable',
            'items.*.admin_discount_rate' => 'nullable|numeric',
            'items.*.admin_discount_amount' => 'nullable|numeric',

            // tax
            'items.*.tax_rate' => 'nullable',
            'items.*.tax_amount' => 'nullable|numeric',

            // qty and price
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $variantAttribute = str_replace('.quantity', '.variant_id', $attribute);
                    $variantId = data_get(request()->all(), $variantAttribute);

                    if ($variantId) {
                        $variant = ProductVariant::find($variantId);

                        if (!$variant) {
                            return $fail("Invalid variant.");
                        }

                        //  Sum qty from product_stocks table across all branches
                        $totalStock = ProductStock::where('variant_id', $variantId)
                            ->sum('qty');

                        if ($totalStock < $value) {
                            return $fail("Only {$totalStock} units available for this variant.");
                        }
                    }
                }
            ],

            'items.*.line_total_price' => 'nullable|numeric',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->getMessages();
        $response = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ];
        throw new HttpResponseException(response()->json($response, 422));
    }
}
