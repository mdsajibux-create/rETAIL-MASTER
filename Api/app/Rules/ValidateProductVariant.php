<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Product\app\Models\ProductVariant;

class ValidateProductVariant implements Rule
{
    protected $productId;
    protected $variantDetails;
    protected $message;

    /**
     * Create a new rule instance.
     *
     * @param int $productId
     * @param array $variantDetails
     */
    public function __construct($productId, $variantDetails)
    {
        $this->productId = $productId;
        $this->variantDetails = $variantDetails;
        $this->message = 'The provided variant details do not match any available variants for the product.';
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!$this->productId || !is_array($this->variantDetails)) {
            return false;
        }

        // Query the product_variants table for the matching product ID
        $product_variant = ProductVariant::where('product_id', $this->productId)->first();

        if (!$product_variant) {
            return false; // Product variant not found
        }

        // Decode the 'attributes' JSON into an associative array
        $product_variant_json_to_array = json_decode($product_variant->attributes, true);

        if (!is_array($product_variant_json_to_array)) {
            return false; // Attributes are not in expected JSON format
        }

        // Now loop through variant details and match with product attributes
        foreach ($this->variantDetails as $key => $value) {
            // Ensure the key exists in the variant's attributes
            if (!isset($product_variant_json_to_array[$key])) {
                return false; // Key not found in the variant's attributes
            }

            // Ensure the value matches the value in the variant's attributes
            if ($product_variant_json_to_array[$key] !== $value) {
                return false; // Value mismatch
            }
        }

        // If all variant details match
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
