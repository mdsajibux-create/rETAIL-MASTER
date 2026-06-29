<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAreaSettingsRequest extends FormRequest
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
            'zone_id' => 'required|exists:zones,id',
            'delivery_time_per_km' => 'required|integer|min:0|max:999999999999',
            'min_order_delivery_fee' => 'nullable|numeric|min:0|max:999999999999',
            'delivery_charge_method' => 'nullable|string|in:fixed,per_km,range_wise',
            'fixed_charge_amount' => 'nullable|numeric|min:0|max:999999999999',
            'per_km_charge_amount' => 'nullable|numeric|min:0|max:999999999999',
            'out_of_area_delivery_charge' => 'nullable|numeric|min:0|max:999999999999',

            // Charges Array Validation
            'charges' => 'nullable|array|min:1',
            'charges.*.min_km' => 'nullable|numeric|min:0|max:999999999999',
            'charges.*.max_km' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $minKm = request()->input("charges.$index.min_km");

                    if ($minKm !== null && $value <= $minKm) {
                        $fail(__('validation.gt', ['attribute' => 'Maximum KM', 'value' => 'Minimum KM']));
                    }
                }
            ],
            'charges.*.charge_amount' => 'nullable|numeric|min:0|max:999999999999',
            'charges.*.status' => 'nullable|boolean',
        ];
    }



    public function messages()
    {
        return [
            'delivery_time_per_km.required' => __('validation.required', ['attribute' => 'Delivery Time Per KM']),
            'delivery_time_per_km.integer' => __('validation.integer', ['attribute' => 'Delivery Time Per KM']),
            'delivery_time_per_km.min' => __('validation.min', ['attribute' => 'Delivery Time Per KM']),

            'min_order_delivery_fee.numeric' => __('validation.numeric', ['attribute' => 'Minimum Order Delivery Fee']),
            'min_order_delivery_fee.min' => __('validation.min', ['attribute' => 'Minimum Order Delivery Fee']),

            'delivery_charge_method.string' => __('validation.string', ['attribute' => 'Delivery Charge Method']),
            'delivery_charge_method.in' => __('validation.in', ['attribute' => 'Delivery Charge Method', 'enum' => 'fixed,per_km,range_wise']),

            'fixed_charge_amount.numeric' => __('validation.numeric', ['attribute' => 'Fixed Amount']),
            'fixed_charge_amount.min' => __('validation.min', ['attribute' => 'Fixed Amount']),

            'per_km_charge_amount.numeric' => __('validation.numeric', ['attribute' => 'Per KM']),
            'per_km_charge_amount.min' => __('validation.min', ['attribute' => 'Per KM']),

            'out_of_area_delivery_charge.numeric' => __('validation.numeric', ['attribute' => 'Out Of Area Delivery Charge']),
            'out_of_area_delivery_charge.min' => __('validation.min', ['attribute' => 'Out Of Area Delivery Charge']),

            'charges.array' => __('validation.array', ['attribute' => 'Charges']),
            'charges.min' => __('validation.min', ['attribute' => 'Charges', 'min' => 1]),

            'charges.*.min_km.numeric' => __('validation.numeric', ['attribute' => 'Minimum KM']),
            'charges.*.min_km.min' => __('validation.min', ['attribute' => 'Minimum KM', 'min' => 0]),

            'charges.*.max_km.numeric' => __('validation.numeric', ['attribute' => 'Maximum KM']),
            'charges.*.max_km.gt' => __('validation.gt', ['attribute' => 'Maximum KM', 'value' => 'Minimum KM']),

            'charges.*.charge_amount.numeric' => __('validation.numeric', ['attribute' => 'Charge Amount']),
            'charges.*.charge_amount.min' => __('validation.min', ['attribute' => 'Charge Amount', 'min' => 0]),
            'charges.*.charge_amount.max' => __('validation.max', ['attribute' => 'Charge Amount', 'max' => 999999999999]),
            'charges.*.status.boolean' => __('validation.boolean', ['attribute' => 'Status']),
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
