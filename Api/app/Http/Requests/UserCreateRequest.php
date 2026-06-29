<?php

namespace App\Http\Requests;

use App\Enums\Role as UserRole;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name'     => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->id,
            'password' => 'required|string',
        ];
    }

    /**
     * Get the error messages that apply to the request parameters.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'first_name.required'      => 'Name is required',
            'first_name.string'        => 'Name is not a valid string',
            'first_name.max:255'       => 'Name can not be more than 255 character',
            'email.required'     => 'email is required',
            'email.email'        => 'email is not a valid email address',
            'email.unique:users' => 'email must be unique',
            'password.required'  => 'password is required',
            'password.string'    => 'password is not a valid string',
            'password.min'    => 'password should be more than 8 character',
            'password.max'    => 'password should not be more than 12 character'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
