<?php

namespace Modules\Location\app\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id'    => ['required', 'integer', 'exists:cities,id'],
            'name'       => ['required', 'string', 'max:100'],
            'zip_code'   => ['nullable', 'string', 'max:20'],
            'is_active'  => ['boolean'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
