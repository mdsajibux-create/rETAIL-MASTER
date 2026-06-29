<?php

namespace Modules\Subscription\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuySubscriptionRequest extends FormRequest
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
            'subscription_id' => 'required|integer|exists:subscriptions,id',
        ];
    }

    /**
     * Customize error messages (optional).
     */
    public function messages(): array
    {
        return [
            'subscription_id.required' => 'The subscription ID is required.',
            'subscription_id.integer' => 'The subscription ID must be an integer.',
            'subscription_id.exists' => 'The selected subscription ID is invalid.',
        ];
    }
}
