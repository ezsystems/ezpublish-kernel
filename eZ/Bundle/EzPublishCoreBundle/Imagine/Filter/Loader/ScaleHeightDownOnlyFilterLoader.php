<?php

/**
 * File containing the ScaleHeightDownOnlyFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;

/**
 * Filter loader for geometry/scaleheightdownonly filter.
 * Proxy to ThumbnailFilterLoader.
 */
class ScaleHeightDownOnlyFilterLoader extends FilterLoaderWrapped
{
    public function load(ImageInterface $image, array $options = [])
    {
        if (empty($options)) {
            throw new InvalidArgumentException('Missing height option');
        }

        return $this->innerLoader->load(
            $image,
            [
                'size' => [null, $options[0]],
                'mode' => ImageInterface::THUMBNAIL_INSET,
            ]
        );
    }
}
