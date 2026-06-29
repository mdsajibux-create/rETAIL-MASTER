<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ContactRequest extends FormRequest
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
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:15',
            'message' => 'required|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.required', ['attribute' => 'Name']),
            'name.string' => __('validation.string', ['attribute' => 'Name']),
            'name.max' => __('validation.max.string', ['attribute' => 'Name', 'max' => '255']),

            'email.required' => __('validation.required', ['attribute' => 'Email']),
            'email.email' => __('validation.email', ['attribute' => 'Email']),
            'email.max' => __('validation.max.string', ['attribute' => 'Email', 'max' => '255']),

            'phone.string' => __('validation.string', ['attribute' => 'Phone']),
            'phone.max' => __('validation.max.string', ['attribute' => 'Phone', 'max' => '15']),

            'message.required' => __('validation.required', ['attribute' => 'Message']),
            'message.string' => __('validation.string', ['attribute' => 'Message']),
            'message.max' => __('validation.max.string', ['attribute' => 'Message', 'max' => '2000']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
