<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReviewRequest extends FormRequest
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
            'order_id' => 'required|exists:orders,id',
            'reviewable_id' => 'required|integer',
            'reviewable_type' => 'required|string|in:product,delivery_man',
            'review' => 'required|string|max:1000',
            'rating' => 'required|numeric|between:1,5',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => __('validation.required', ['attribute' => 'Order ID']),
            'order_id.exists' => __('validation.exists', ['attribute' => 'Order ID']),

            'reviewable_id.required' => __('validation.required', ['attribute' => 'Reviewable ID']),
            'reviewable_id.integer' => __('validation.integer', ['attribute' => 'Reviewable ID']),

            'reviewable_type.required' => __('validation.required', ['attribute' => 'Reviewable Type']),
            'reviewable_type.string' => __('validation.string', ['attribute' => 'Reviewable Type']),
            'reviewable_type.in' => __('validation.in', ['attribute' => 'Reviewable Type']),

            'review.required' => __('validation.required', ['attribute' => 'Review']),
            'review.string' => __('validation.string', ['attribute' => 'Review']),
            'review.max' => __('validation.max', ['attribute' => 'Review']),

            'rating.required' => __('validation.required', ['attribute' => 'Rating']),
            'rating.numeric' => __('validation.numeric', ['attribute' => 'Rating']),
            'rating.between' => __('validation.between', ['attribute' => 'Rating']),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
