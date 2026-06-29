<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CouponLineRequest extends FormRequest
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
            'coupon_id' => 'required|exists:coupons,id',
            'customer_id' => 'nullable|exists:customers,id',
            'discount_type' => 'required|string|in:percentage,amount',
            'discount' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'usage_count' => 'nullable|integer|min:0',
            'start_date' => 'required|date|date_format:Y-m-d H:i:s',
            'end_date' => 'required|date|after_or_equal:start_date|date_format:Y-m-d H:i:s',

        ];
    }
    public function messages()
    {
        return [
            'coupon_id' => __('validation.required', ['attribute' => 'Coupon']),
            'coupon_id.exists' => __('validation.exists', ['attribute' => 'Coupon']),
            'customer_id.exists' => __('validation.exists', ['attribute' => 'Customer']),
            'discount_type.required' => __('validation.required', ['attribute' => 'Discount Type']),
            'discount_type.in' => __('validation.in', ['attribute' => 'Discount Type', 'enum' => 'percentage or amount']),
            'discount.required' => __('validation.required', ['attribute' => 'Discount']),
            'discount.numeric' => __('validation.numeric', ['attribute' => 'Discount']),
            'min_order_value.numeric' => __('validation.numeric', ['attribute' => 'Minimum Order Value']),
            'usage_limit.integer' => __('validation.integer', ['attribute' => 'Usage Limit']),
            'usage_count.integer' => __('validation.integer', ['attribute' => 'Usage Count']),
            'start_date.required' => __('validation.required', ['attribute' => 'Start Date']),
            'start_date.date_format' => __('validation.date_format', ['attribute' => 'Start Date']),
            'end_date.required' => __('validation.required', ['attribute' => 'End Date']),
            'end_date.date_format' => __('validation.date_format', ['attribute' => 'End Date']),
            'end_date.after_or_equal' => __('validation.after_or_equal', ['attribute' => 'End Date']),
            'status.required' => __('validation.required', ['attribute' => 'Status']),

        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
