<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class TagRequest extends FormRequest
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
            "name" => "required",
            "order" => "required|integer|unique:tags,order," . $this->id,
        ];
    }

    public function messages()
    {
        return [
            "name.required" => __('validation.required', ['attribute' => 'name']),
            "order.required" => __('validation.required', ["attribute" => "Order"]),
            "order.integer" => __('validation.integer', ['attribute' => 'order']),
            "order.unique" => __('validation.unique', ['attribute' => 'Display order']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
