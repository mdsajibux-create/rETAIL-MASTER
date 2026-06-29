<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'role_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->role_id), // ignore current role ID
            ],
            'permissions.*.id'     => 'required|integer|exists:permissions,id',
            'permissions.*.view'   => 'nullable|boolean',
            'permissions.*.insert' => 'nullable|boolean',
            'permissions.*.update' => 'nullable|boolean',
            'permissions.*.delete' => 'nullable|boolean',
            'permissions.*.others' => 'nullable',
            'available_for' => 'nullable|string|max:255|in:system_level,branch_level,delivery_level',
            'permissions.*' => 'nullable',
        ];
    }

    /**
     * Handle failed validation response for API
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'status'  => false,
            'message' => 'Validation errors',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
