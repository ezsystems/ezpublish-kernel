<?php

/**
 * File containing the ScaleDownFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scaledownonly filter.
 * Proxy to ThumbnailFilterLoader.
 */
class ScaleDownOnlyFilterLoader extends FilterLoaderWrapped
{
    /**
     * Loads and applies a filter on the given image.
     *
     * @param ImageInterface $image
     * @param array $options Numerically indexed array. First entry is width, second is height.
     *
     * @throws \Imagine\Exception\InvalidArgumentException
     *
     * @return ImageInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        if (count($options) < 2) {
            throw new InvalidArgumentException('Missing width and/or height options');
        }

        return $this->innerLoader->load(
            $image,
            [
                'size' => $options,
                'mode' => ImageInterface::THUMBNAIL_INSET,
            ]
        );
    }
}
