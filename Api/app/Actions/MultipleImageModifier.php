<?php

namespace App\Actions;

class MultipleImageModifier
{
    /**
     * Generate the image URL from the given image identifier.
     *
     * @param mixed $image
     * @return string|null
     */
    public static function multipleImageModifier($multiple_images){

        if (empty($multiple_images)) {
            return null;
        }

        if (is_array($multiple_images)) {
            $img_ids = $multiple_images;
        } else {
            $img_ids = explode(',', $multiple_images);
        }

        $img_urls = [];

        foreach ($img_ids as $img_id) {
            $img_id = trim((string) $img_id);

            if ($img_id === '') {
                continue;
            }

            $image_url = com_option_get_id_wise_url($img_id);

            if ($image_url) {
                $img_urls[] = $image_url;
            }
        }

        return !empty($img_urls) ? $img_urls : null;
    }
}
