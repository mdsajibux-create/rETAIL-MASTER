<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SliderRequest extends FormRequest
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
            'theme_name' => 'required|string|max:255|in:one,two,three,four,five',
            'title' => 'required|string|max:255',
            'title_color' => 'nullable|string|max:7', // Assuming HEX color code
            'sub_title' => 'nullable|string|max:255',
            'sub_title_color' => 'nullable|string|max:7', // Assuming HEX color code
            'description' => 'nullable|string',
            'description_color' => 'nullable|string|max:7', // Assuming HEX color code
            'image' => 'nullable', // Validating image upload
            'bg_image' => 'nullable', // Validating image upload
            'button_text' => 'nullable|string|max:50',
            'bg_color' => 'nullable|string|max:7', // Assuming HEX color code
            'button_bg_color' => 'nullable|string|max:7', // Assuming HEX color code
            'button_hover_color' => 'nullable|string|max:7', // Assuming HEX color code
            'button_url' => 'nullable|url|max:255',
            'redirect_url' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
            'created_by' => 'nullable|string|max:255',
            'updated_by' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'platform.required' => __('validation.required'),
            'platform.in' => __('validation.in', ['enum' => 'web,mobile']),

            'title.required' => __('validation.required'),
            'title.string' => __('validation.string'),
            'title.max' => __('validation.max.string'),

            'title_color.string' => __('validation.string'),
            'title_color.max' => __('validation.max.string'),

            'sub_title.string' => __('validation.string'),
            'sub_title.max' => __('validation.max.string'),

            'sub_title_color.string' => __('validation.string'),
            'sub_title_color.max' => __('validation.max.string'),

            'description.string' => __('validation.string'),

            'description_color.string' => __('validation.string'),
            'description_color.max' => __('validation.max.string'),

            'image.image' => __('validation.image'),
            'image.mimes' => __('validation.mimes'),
            'image.max' => __('validation.max.file'),

            'button_text.string' => __('validation.string'),
            'button_text.max' => __('validation.max.string'),

            'button_text_color.string' => __('validation.string'),
            'button_text_color.max' => __('validation.max.string'),

            'button_bg_color.string' => __('validation.string'),
            'button_bg_color.max' => __('validation.max.string'),

            'bg_color.string' => __('validation.string'),
            'bg_color.max' => __('validation.max.string'),

            'button_hover_color.string' => __('validation.string'),
            'button_hover_color.max' => __('validation.max.string'),

            'button_url.url' => __('validation.url'),
            'button_url.max' => __('validation.max.string'),

            'redirect_url.url' => __('validation.url'),
            'redirect_url.max' => __('validation.max.string'),

            'order.integer' => __('validation.integer'),
            'order.min' => __('validation.min.numeric'),
            'order.unique' => __('validation.unique'),

            'status.integer' => __('validation.integer'),
            'status.in' => __('validation.in'),

            'created_by.string' => __('validation.string'),
            'created_by.max' => __('validation.max.string'),

            'updated_by.string' => __('validation.string'),
            'updated_by.max' => __('validation.max.string'),
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
