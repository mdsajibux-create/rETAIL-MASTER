<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CommentRequest extends FormRequest
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
            'blog_id' => 'required|exists:blogs,id',
            'comment' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'blog_id.required' => __('validation.required'),
            'blog_id.exists' => __('validation.exists'),
            'comment.required' => __('validation.required'),
            'comment.string' => __('validation.string'),
            'comment.max' => __('validation.max.string'),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
