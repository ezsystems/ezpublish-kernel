<?php

/**
 * File containing the BorderFilterLoad class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Filter\Advanced\Border;
use Imagine\Image\ImageInterface;
use Imagine\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Loader for border filter.
 * Adds a border around the image.
 *
 * Note: Does not work properly with GD.
 */
class BorderFilterLoader implements LoaderInterface
{
    const DEFAULT_BORDER_COLOR = '#000';

    public function load(ImageInterface $image, array $options = [])
    {
        $optionsCount = count($options);
        if ($optionsCount < 2) {
            throw new InvalidArgumentException('Invalid options for border filter. You must provide array(width, height)');
        }

        $color = static::DEFAULT_BORDER_COLOR;
        if ($optionsCount > 2) {
            list($width, $height, $color) = $options;
        } else {
            list($width, $height) = $options;
        }

        $border = new Border($image->palette()->color($color), $width, $height);

        return $border->apply($image);
    }
}
