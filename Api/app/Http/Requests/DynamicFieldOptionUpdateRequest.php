<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DynamicFieldOptionUpdateRequest extends FormRequest
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
            "dynamic_field_id" => "required|exists:dynamic_fields,id",
            "id"        => "required|exists:dynamic_field_values,id",
            "value"        => "required|string|max:255",
            "translations" => "nullable|array",
        ];
    }
    public function messages()
    {
        return [
            "value.required"       => "Value field is required!",
            "translations.array"  => "Translations must be sent as an array.",
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
