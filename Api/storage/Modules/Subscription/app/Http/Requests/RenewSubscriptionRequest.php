<?php

namespace Modules\Subscription\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RenewSubscriptionRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'subscription_id' => [
                'required',
                'integer',
                'exists:subscriptions,id', // Adjust table name if needed
            ],
            'payment_gateway' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'store_id.required' => 'The store ID is required.',
            'store_id.integer' => 'The store ID must be a valid integer.',
            'store_id.exists' => 'The specified store does not exist in our records.',
            'subscription_id.required' => 'The subscription ID is required.',
            'subscription_id.integer' => 'The subscription ID must be a valid integer.',
            'subscription_id.exists' => 'The specified subscription does not exist in our records.',
            'payment_gateway.required' => 'The payment gateway is required.',
        ];
    }

    /**
     * Customize the failed validation response.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->getMessages();

        $response = [
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ];

        throw new HttpResponseException(response()->json($response, 422));
    }


}
