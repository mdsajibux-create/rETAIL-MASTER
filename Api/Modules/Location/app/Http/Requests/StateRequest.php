<?php

namespace Modules\Location\app\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $stateId = $this->input('id') ?? $this->route('id') ?? null;

        return [
            'name'       => ['required', 'string', 'max:100'],
            'code'       => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('states', 'code')
                    ->ignore($stateId) // ignore current state on update
                    ->whereNull('deleted_at')
            ],
            'is_active'  => ['boolean'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'sort_order' => ['integer', 'min:0'],
            'translations' => ['sometimes','array'],
        ];
    }


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

}
