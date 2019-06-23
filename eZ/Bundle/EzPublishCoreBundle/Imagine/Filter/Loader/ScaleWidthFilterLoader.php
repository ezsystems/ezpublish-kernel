<?php

/**
 * File containing the ScaleWidthFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scalewidth filter.
 * Proxy to RelativeResizeFilterLoader.
 */
class ScaleWidthFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = [])
    {
        if (empty($options)) {
            throw new InvalidArgumentException('Missing width option');
        }

        return $this->innerLoader->load($image, ['widen' => $options[0]]);
    }
}
