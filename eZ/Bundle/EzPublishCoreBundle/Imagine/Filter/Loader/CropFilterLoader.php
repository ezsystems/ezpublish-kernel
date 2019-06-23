<?php

/**
 * File containing the CropFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/crop filter.
 * Proxy to CropFilterLoader.
 */
class CropFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = [])
    {
        if (count($options) < 4) {
            throw new InvalidArgumentException('Invalid options for geometry/crop filter. You must provide array(width, height, offsetX, offsetY)');
        }

        return $this->innerLoader->load(
            $image,
            [
                'size' => [$options[0], $options[1]],
                'start' => [$options[2], $options[3]],
            ]
        );
    }
}
