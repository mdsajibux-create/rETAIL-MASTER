<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class DynamicFieldRequest extends FormRequest
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

        $id = $this->route('id') ?? $this->id;
        return [
            "name"        => "required|string|max:255",
            "slug"        => [
                "required",
                "string",
                "max:255",
                Rule::unique('dynamic_fields', 'slug')->ignore($id)
            ],
            "product_type"  => "required|string|max:255|in:flower,furniture",
            "type"        => "required|in:text,textarea,select,multiselect,number,date,time,color,boolean,checkbox,radio",
            "options"     => "nullable", // must be array if provided
            "is_required" => "nullable|boolean",
            "status" => "nullable|string",
            "translations" => "nullable|array", // extra field
        ];
    }
    public function messages()
    {
        return [
            "name.required"       => "Name field is required!",
            "slug.required"       => "Slug field is required!",
            "slug.unique"         => "Slug must be unique!",
            "product_type.required" => "Product type is required!",
            "type.required"       => "Type is required!",
            "type.in"             => "Invalid type selected!",
            "options.array"       => "Options must be an array format.",
            "translations.array"  => "Translations must be sent as an array.",
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
