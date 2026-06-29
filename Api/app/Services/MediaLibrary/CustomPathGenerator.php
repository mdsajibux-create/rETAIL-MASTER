<?php

namespace App\Services\MediaLibrary;

use \Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator as BasePathGenerator;

class CustomPathGenerator implements BasePathGenerator
{

    private function getFilePath(Media $media, ?string $extra = null): string
    {

        return $this->getBasePath($media).'/';
    }

    /**
     * Get the path for the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPath(Media $media): string
    {
        return $this->getFilePath($media);
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     *
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media $media
     *
     * @return string
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    protected function getBasePath(Media $media): string
    {
        if($media->hasCustomProperty('dir_name'))
        {
           return $media->getCustomProperty('dir_name',$media->getKey());
        }
        else {

            return $this->getBasePath($media);
        }       

    }
}
