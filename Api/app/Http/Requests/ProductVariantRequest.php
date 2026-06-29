<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductVariantRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'variant_slug' => 'nullable|string|max:255|unique:product_variants,variant_slug,' . $this->id,
            'sku' => 'nullable|string|max:255|unique:product_variants,sku,' . $this->id,
            'pack_quantity' => 'nullable|numeric|min:0',
            'weight_major' => 'nullable|numeric|min:0',
            'weight_gross' => 'nullable|numeric|min:0',
            'weight_net' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0|lte:price',
            'stock_quantity' => 'required|integer|min:0',
            'unit_id' => 'nullable',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'image' => 'nullable',
            'order_count' => 'nullable|integer|min:0',
            'status' => 'required|integer|in:0,1',
        ];
    }
    public function messages()
    {
        return [
            'product_id.required' => 'The product is required. ',
            'product_id.exists' => 'The selected product does not exist.',
            'variant_slug.unique' => 'The variant slug must be unique.',
            'sku.unique' => 'The SKU must be unique.',
            'special_price.lte' => 'The special price must be less than or equal to the base price.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'status.in' => 'The status must be either 0 (inactive) or 1 (active).',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
