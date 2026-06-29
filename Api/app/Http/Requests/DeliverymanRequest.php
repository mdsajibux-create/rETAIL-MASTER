<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DeliverymanRequest extends FormRequest
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
            'phone' => 'nullable|string|max:15',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->id,
            'password' => 'required|string',
            'status' => 'nullable|integer',
            'vehicle_type_id' => 'nullable|exists:vehicle_types,id',
            'identification_type' => 'nullable|string|in:nid,passport,driving_license',
            'identification_number' => 'nullable|string',
            'zone_id' => 'nullable',
            'state_id' => 'nullable',
            'city_id' => 'nullable',
            'area_id' => 'nullable',
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => __('validation.required', ['attribute' => 'First name']),
            'first_name.string' => __('validation.string', ['attribute' => 'First name']),
            'first_name.max' => __('validation.max.string', ['attribute' => 'First name']),
            'last_name.string' => __('validation.string', ['attribute' => 'Last name']),
            'last_name.max' => __('validation.max.string', ['attribute' => 'Last name']),
            'phone.string' => __('validation.string', ['attribute' => 'Phone']),
            'phone.max' => __('validation.max.string', ['attribute' => 'Phone']),
            'email.required' => __('validation.required', ['attribute' => 'Email']),
            'email.string' => __('validation.string', ['attribute' => 'Email']),
            'email.email' => __('validation.email', ['attribute' => 'Email']),
            'email.max' => __('validation.max.string', ['attribute' => 'Email']),
            'email.unique' => __('validation.unique', ['attribute' => 'Email']),
            'password.required' => __('validation.required', ['attribute' => 'Password']),
            'password.min' => __('validation.min.string', ['attribute' => 'Password']),
            'password.max' => __('validation.max.string', ['attribute' => 'Password']),
            'password.string' => __('validation.string', ['attribute' => 'Password']),
            'status.integer' => __('validation.integer', ['attribute' => 'Status']),
            'vehicle_type_id.required' => __('validation.required', ['attribute' => 'Vehicle type']),
            'vehicle_type_id.exists' => __('validation.exists', ['attribute' => 'Vehicle type']),
            'area_id.exists' => __('validation.exists', ['attribute' => 'Area']),
            'identification_type.required' => __('validation.required', ['attribute' => 'Identification type']),
            'identification_type.string' => __('validation.string', ['attribute' => 'Identification type']),
            'identification_type.in' => __('validation.in', ['attribute' => 'Identification type', 'enum' => 'nid,passport,driving_license']),
            'identification_number.required' => __('validation.required', ['attribute' => 'Identification number']),
            'identification_number.string' => __('validation.string', ['attribute' => 'Identification number']),
            'address.string' => __('validation.string', ['attribute' => 'Address']),
            'address.max' => __('validation.max.string', ['attribute' => 'Address']),

        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
