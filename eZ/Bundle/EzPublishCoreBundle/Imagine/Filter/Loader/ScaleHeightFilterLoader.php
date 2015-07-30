<?php

/**
 * File containing the ScaleHeightFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scaleheight filter.
 * Proxy to RelativeResizeFilterLoader.
 */
class ScaleHeightFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = array())
    {
        if (empty($options)) {
            throw new InvalidArgumentException('Missing width option');
        }

        return $this->innerLoader->load($image, array('heighten' => $options[0]));
    }
}
