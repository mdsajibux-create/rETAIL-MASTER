<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductAuthorRequest extends FormRequest
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
            "born_date" => "required|date_format:Y-m-d",
            "death_date"=> "nullable|date_format:Y-m-d",
        ];
    }
    public function messages()
    {
        return [
            "name.required" => "Name field is required!",
            "born_date.required" => "Born date field is required!",
            "born_date.date_format"=> "Incorrect date format!",
            "death_date.date_format"=> "Incorrect date format!",
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
