<?php

/**
 * File containing the GrayscaleFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Grayscale filter loader.
 * Makes an image use grayscale.
 */
class GrayscaleFilterLoader implements LoaderInterface
{
    public function load(ImageInterface $image, array $options = array())
    {
        $image->effects()->grayscale();

        return $image;
    }
}
