<?php

/**
 * File containing the ScaleFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scale filter.
 * Proxy to RelativeResizeFilterLoader.
 */
class ScaleFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = [])
    {
        if (count($options) < 2) {
            throw new InvalidArgumentException('Missing width and/or height options');
        }

        list($width, $height) = $options;
        $size = $image->getSize();
        $ratioWidth = $width / $size->getWidth();
        $ratioHeight = $height / $size->getHeight();

        // We shall use the side which has the lowest ratio with target value
        // as $width and $height are always maximum values.
        if ($ratioWidth <= $ratioHeight) {
            $method = 'widen';
            $value = $width;
        } else {
            $method = 'heighten';
            $value = $height;
        }

        return $this->innerLoader->load($image, [$method => $value]);
    }
}
