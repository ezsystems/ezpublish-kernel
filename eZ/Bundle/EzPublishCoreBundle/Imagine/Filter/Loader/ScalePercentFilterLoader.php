<?php

/**
 * File containing the ScalePercentFilterLoad class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scaleexact filter.
 * Proxy to ThumbnailFilterLoader.
 */
class ScalePercentFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = [])
    {
        if (count($options) < 2) {
            throw new InvalidArgumentException('Missing width and/or height percent options');
        }

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();
        list($widthPercent, $heightPercent) = $options;

        $targetWidth = ($origWidth * $widthPercent) / 100;
        $targetHeight = ($origHeight * $heightPercent) / 100;

        return $this->innerLoader->load($image, ['size' => [$targetWidth, $targetHeight]]);
    }
}
