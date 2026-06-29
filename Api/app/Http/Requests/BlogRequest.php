<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class BlogRequest extends FormRequest
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
        $isUpdate = !empty($this->id); // If $this->id exists, it's an update
        return [
            'admin_id' => 'nullable|exists:users,id',
            'category_id' => 'nullable|exists:blog_categories,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blogs,slug,' . $this->id,
            'description' => 'required|string',
            'image' => 'nullable',
            'views' => 'nullable|integer|min:0',
            'visibility' => 'nullable',
            'status' => 'required|boolean',
            'schedule_date' => array_filter([
                'nullable',
                'date',
                !$isUpdate ? 'after_or_equal:today' : null,
                'date_format:Y-m-d',
            ]),
            'tag_name' => 'nullable',
            'meta_title' => 'nullable',
            'meta_description' => 'nullable',
            'meta_keywords' => 'nullable',
            'meta_image' => 'nullable',
        ];
    }
    public function messages()
    {
        return [
            'admin_id.exists' => __('validation.exists', ['attribute' => 'User']),
            'category_id.exists' => __('validation.exists', ['attribute' => 'Category']),
            'title.required' => __('validation.required'),
            'title.string' => __('validation.string'),
            'title.max' => __('validation.max.string'),
            'slug.required' => __('validation.required'),
            'slug.string' => __('validation.string'),
            'slug.max' => __('validation.max.string'),
            'slug.unique' => __('validation.unique'),
            'description.required' => __('validation.required'),
            'description.string' => __('validation.string'),
            'views.integar' => __('validation.integar'),
            'views.min' => __('validation.min_digits'),
            'visibility.in' => __('validation.in', ['enum' => 'public, private']),
            'status.required' => __('validation.required'),
            'status.boolean' => __('validation.boolean'),
            'schedule_date.date' => __('validation.date'),
            'schedule_date.after_or_equal' => __('validation.after_or_equal'),
            'schedule_date.date_format' => __('validation.date_format'),
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }


}
