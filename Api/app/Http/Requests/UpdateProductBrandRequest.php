<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductBrandRequest extends FormRequest
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
            'brand_name' => 'required|string|max:255',
            'brand_slug' => 'required|string|max:255|unique:product_brand,brand_slug,' . $this->route('product_brand'),
            'brand_logo' => 'nullable|string|max:255',
            'brand_logo_url' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'seller_relation_with_brand' => 'nullable|string|max:255',
            'authorization_valid_from' => 'nullable|date',
            'authorization_valid_to' => 'nullable|date',
            'display_order' => 'nullable|integer',
            'status' => 'boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
