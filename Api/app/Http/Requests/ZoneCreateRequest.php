<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ZoneCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                    => 'required|string|max:255',
            'code'                    => 'nullable|string|max:50|unique:zones,code',
            'state'                   => 'nullable|string|max:255',
            'city'                    => 'nullable|string|max:255',
            'status'                  => 'nullable|boolean',
            'coordinates'             => 'nullable|array|min:3',
            'coordinates.*.lat'       => 'nullable|numeric|between:-90,90',
            'coordinates.*.lng'       => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Get the error messages that apply to the request parameters.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required'            => __('validation.required', ['attribute' => 'zone name']),
            'name.string'              => __('validation.string', ['attribute' => 'zone name']),
            'name.max'                 => __('validation.max.string', ['attribute' => 'zone name']),
            'code.required'            => __('validation.nullable', ['attribute' => 'zone code']),
            'code.unique'              => __('validation.nullable.unique', ['attribute' => 'zone code']),
            'state.required'           => __('validation.nullable', ['attribute' => 'state']),
            'city.required'            => __('validation.nullable', ['attribute' => 'city']),
            'coordinates.required'     => __('validation.nullable', ['attribute' => 'coordinates']),
            'coordinates.min'          => __('validation.min.array', ['attribute' => 'coordinates', 'min' => 3]),
            'coordinates.*.lat.required' => __('validation.nullable', ['attribute' => 'latitude']),
            'coordinates.*.lng.required' => __('validation.nullable', ['attribute' => 'longitude']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
