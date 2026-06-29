<?php

namespace Modules\Pos\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class PosOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'payment_gateway' => ['required', 'string', 'in:cash,card,wallet'],
            'coupon_code' => ['nullable', 'string', 'exists:coupon_lines,coupon_code'],
            'package' => ['required', 'array', 'min:1'],
            'package.*.items' => ['required', 'array', 'min:1'],
            'package.*.items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'package.*.items.*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'package.*.items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'package.required' => 'At least one package is required.',
            'package.*.items.required' => 'Each package must contain at least one item.',
            'package.*.delivery_option.required' => 'Each package must include a delivery option.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
