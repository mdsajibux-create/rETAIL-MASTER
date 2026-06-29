<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SupportTicketRequest extends FormRequest
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
            'department_id' => 'required|exists:departments,id',
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'nullable|in:low,high,medium,urgent',
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => __('validation.required', ['attribute' => 'Department']),
            'department_id.exists' => __('validation.exists', ['attribute' => 'Department']),
            'title.required' => __('validation.required', ['attribute' => 'Title']),
            'title.string' => __('validation.string', ['attribute' => 'Title']),
            'title.max' => __('validation.max.string', ['attribute' => 'Title']),
            'subject.required' => __('validation.required', ['attribute' => 'Subject']),
            'subject.string' => __('validation.string', ['attribute' => 'Subject']),
            'subject.max' => __('validation.max.string', ['attribute' => 'Subject']),
            'priority.in' => __('validation.in', ['attribute' => 'Priority']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
