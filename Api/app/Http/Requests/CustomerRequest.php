<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CustomerRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|unique:customers,phone',
            'password' => 'required|string|min:8|max:32',
            'birth_day' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'nullable|in:' . $this->getEnumValues(Gender::class),
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('validation.required', ['attribute' => 'First Name']),
            'first_name.string' => __('validation.string', ['attribute' => 'First Name']),
            'first_name.max' => __('validation.max.string', ['attribute' => 'First Name']),
            'last_name.string' => __('validation.string', ['attribute' => 'Last Name']),
            'last_name.max' => __('validation.max.string', ['attribute' => 'Last Name']),
            'email.required' => __('validation.required', ['attribute' => 'Email']),
            'email.email' => __('validation.email', ['attribute' => 'Email']),
            'email.unique' => __('validation.unique', ['attribute' => 'Email']),
            'phone.string' => __('validation.string', ['attribute' => 'Phone']),
            'phone.unique' => __('validation.unique', ['attribute' => 'Phone']),
            'password.required' => __('validation.required', ['attribute' => 'Password']),
            'birth_day.date' => __('validation.date', ['attribute' => 'Birth Day']),
            'birth_day.date_format' => __('validation.format', ['attribute' => 'Birth Day']),
            'gender.in' => __('validation.in', ['attribute' => 'Gender', 'enum' => $this->getEnumValues(Gender::class)]),
        ];
    }

    private function getEnumValues(string $enumClass): string
    {
        return implode(',', array_map(fn($case) => $case->value, $enumClass::cases()));

    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['message'=>$validator->errors()], 422));
    }
}
