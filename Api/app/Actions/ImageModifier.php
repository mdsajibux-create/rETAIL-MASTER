<?php

namespace App\Actions;

class ImageModifier
{
    /**
     * Generate the image URL from the given image identifier.
     *
     * @param mixed $image
     * @return string|null
     */
    public static function generateImageUrl($image){
        if (!empty($image)) {
            return com_option_get_id_wise_url($image);
        }
        return null;
    }
}
