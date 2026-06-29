<?php

namespace App\Http\Requests;

use App\Enums\FuelType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VehicleTypeRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $this->id,
            'capacity' => 'nullable|integer|min:0',
            'speed_range' => 'nullable|string',
            'fuel_type' => 'nullable|in:' . implode(',', array_column(FuelType::cases(), 'value')),
            'max_distance' => 'nullable|integer|min:0',
            'extra_charge' => 'nullable|numeric|min:0',
            'average_fuel_cost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:5000',
            'status' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.required', ['attribute' => 'Vehicle name']),
            'name.string' => __('validation.string', ['attribute' => 'Vehicle name']),
            'name.unique' => __('validation.unique', ['attribute' => 'Vehicle name']),
            'name.max' => __('validation.max.string', ['attribute' => 'Vehicle name']),
            'capacity.integer' => __('validation.integer', ['attribute' => 'Vehicle capacity']),
            'capacity.min' => __('validation.min.integer', ['attribute' => 'Vehicle capacity']),
            'speed_range.string' => __('validation.string', ['attribute' => 'Vehicle speed range']),
            'fuel_type.in' => __('validation.in', ['attribute' => 'Vehicle fuel type', 'enum' => implode(',', array_column(FuelType::cases(), 'value'))]),
            'max_distance.integer' => __('validation.integer', ['attribute' => 'Max distance value']),
            'max_distance.min' => __('validation.min.integer', ['attribute' => 'Max distance value']),
            'extra_charge.numeric' => __('validation.numeric', ['attribute' => 'Extra charge value']),
            'extra_charge.min' => __('validation.min.integer', ['attribute' => 'Extra charge value']),
            'average_fuel_cost.numeric' => __('validation.numeric', ['attribute' => 'Average fuel cost']),
            'average_fuel_cost.min' => __('validation.numeric', ['attribute' => 'Average fuel cost']),
            'description.string' => __('validation.string', ['attribute' => 'Description']),
            'description.max' => __('validation.max.string', ['attribute' => 'Description']),
            'status.boolean' => __('validation.boolean', ['attribute' => 'Status']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
