<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductQueryRequest extends FormRequest
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
            'question' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => __('validation.required', ['attribute' => 'Product ID']),
            'product_id.exists' => __('validation.exists', ['attribute' => 'Product ID']),
            'question.required' => __('validation.required', ['attribute' => 'Question']),
            'question.string' => __('validation.string', ['attribute' => 'Question']),
            'question.max' => __('validation.max.string', ['attribute' => 'Question']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['message' => $validator->errors()], 422));
    }
}
