<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductAttributeRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'product_type' => 'nullable|in:' . implode(',', array_column(ProductType::cases(), 'value')),
            'value' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.required', ['name' => 'Name']),
            'name.string' => __('validation.string', ['name' => 'Name']),
            'name.max' => __('validation.max.string', ['name' => 'Name']),
            'product_type.in' => __('validation.in', ['name' => 'Product Type', 'enum' => implode(',', array_column(ProductType::cases(), 'value'))]),
            'value.required' => __('validation.required', ['value' => 'Value']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
