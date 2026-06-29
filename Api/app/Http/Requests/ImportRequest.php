<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,csv|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('validation.required'),
            'file.file' => __('validation.file'),
            'file.mimes' => __('validation.mimes'),
            'file.max' => __('validation.max.file'),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
