<?php

namespace App\Http\Requests;

use App\Enums\Behaviour;
use App\Enums\BranchType;
use App\Enums\StatusType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $rules = [
            "id" => "required|exists:products,id",
            "category_id" => "required|exists:product_category,id",
            "brand_id" => "nullable|exists:product_brand,id",
            "unit_id" => "nullable|exists:units,id",
            "type" => "required|in:furniture,flower",
            "behaviour" => "nullable|in:" . implode(',', array_column(Behaviour::cases(), 'value')),
            "name" => "required|string|max:255",
            "description" => "nullable|string",
            "image" => "nullable|integer|max:255",
            "video_url" => "nullable|string|max:500",
            "class" => "nullable|string|max:255",
            "warranty" => "nullable|array",
            "warranty.*.warranty_period" => "nullable|integer",
            "warranty.*.warranty_text" => "nullable|string|max:255",
            "return_in_days" => "nullable|string|max:255",
            "return_text" => "nullable|string|max:255",
            "allow_change_in_mind" => "nullable|max:255",
            "cash_on_delivery" => "nullable|integer|min:0|max:100",
            "delivery_time_min" => "nullable|string|max:255",
            "delivery_time_max" => "nullable|string|max:255",
            "delivery_time_text" => "nullable|string|max:255",
            "max_cart_qty" => "nullable|integer|min:1",
            "order_count" => "nullable|integer|min:0",
            "views" => "nullable|integer|min:0",
            "status" => "nullable|in:" . implode(',', array_column(StatusType::cases(), 'value')),
            "meta_title" => "nullable|string|max:255",
            "meta_description" => "nullable|string",
            "meta_keywords" => "nullable",
            "meta_image" => "nullable|string|max:255",
            "available_time_starts" => "nullable|date",
            "available_time_ends" => "nullable|date|after_or_equal:available_time_starts",
            "manufacture_date" => "nullable|date",
            "expiry_date" => "nullable|date|after_or_equal:expiry_date",
            "specifications" => "nullable|array",
            'specifications.*.dynamic_field_id' => 'nullable|integer|exists:dynamic_fields,id',
            'specifications.*.dynamic_field_value_id' => 'nullable|integer|exists:dynamic_field_values,id',
            'specifications.*.custom_value' => 'nullable|string|max:1200',
            'specifications.*.type' => 'nullable|string|in:text,textarea,number,date,time,boolean,select,multiselect,checkbox,radio',
            "variants" => "required|array|min:1",
            "variants.*.variant_slug" => "nullable|string|max:255|unique:product_variants,variant_slug," . ($this->route('product') ?? 0) . ",id",
            "variants.*.pack_quantity" => "nullable|numeric|min:0",
            "variants.*.weight_major" => "nullable|numeric|min:0",
            "variants.*.weight_gross" => "nullable|numeric|min:0",
            "variants.*.weight_net" => "nullable|numeric|min:0",
            "variants.*.attributes" => "nullable|array",
            "variants.*.price" => "nullable|numeric|min:0",
            "variants.*.special_price" => "nullable|numeric|min:0|lte:variants.*.price",
            "variants.*.unit_id" => "nullable|exists:units,id",
            "variants.*.length" => "nullable|numeric|min:0",
            "variants.*.width" => "nullable|numeric|min:0",
            "variants.*.height" => "nullable|numeric|min:0",
            "variants.*.image" => "nullable|integer|max:255",
            "variants.*.order_count" => "nullable|integer|min:0",
            "variants.*.status" => "nullable|integer|in:0,1",
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            "category_id.required" => "Category is required.",
            "category_id.exists" => "The selected category does not exist.",
            "brand_id.exists" => "The selected brand does not exist.",
            "unit_id.exists" => "The selected unit does not exist.",
            "name.required" => "The product name is required.",
            "status.required" => "The status is required.",
            "type.in" => "The selected type is invalid.",
            "behaviour.in" => "The selected behaviour is invalid.",
            "delivery_time_max.gte" => "The max delivery time must be greater than or equal to min delivery time.",
            "manufacture_date.date" => "The manufacture date must be a date.",
            "expiry_date.date" => "The expiry date must be a date.",
            "expiry_date.after_or_equal" => "The expiry date must be after or equal to manufacture date.",
            "variants.*.variant_slug.unique" => "Each variant slug must be unique.",
            "variants.*.sku.unique" => "Each SKU must be unique.",
            "variants.*.stock_quantity.required" => "Stock quantity is required for each variant.",
            "variants.*.special_price.lte" => "Special price must be less than or equal to the price.",
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    public function withValidator(Validator $validator): void
    {
        if (!shouldRound()) {
            return;
        }

        $validator->after(function ($validator) {
            foreach ($this->input('variants', []) as $index => $variant) {
                if (
                    isset($variant['price']) &&
                    is_numeric($variant['price']) &&
                    is_float($variant['price'])
                ) {
                    $validator->errors()->add("variants.$index.price", __('messages.should_round', ['name' => 'Price']));
                }

                if (
                    isset($variant['special_price']) &&
                    is_numeric($variant['special_price']) &&
                    is_float($variant['special_price'])
                ) {
                    $validator->errors()->add("variants.$index.special_price", __('messages.should_round', ['name' => 'Special price']));
                }
            }
        });
    }
}
