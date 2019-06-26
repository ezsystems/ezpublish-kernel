<?php

/**
 * File containing the ReduceNoiseFilterLoader class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Imagine\Exception\NotSupportedException;
use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;
use Imagine\Gmagick\Image as GmagickImage;
use Imagine\Imagick\Image as ImagickImage;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

/**
 * Noise reduction filter loader.
 * Only works with Imagick / Gmagick.
 */
class ReduceNoiseFilterLoader implements LoaderInterface
{
    /** @var FilterInterface */
    private $filter;

    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function load(ImageInterface $image, array $options = [])
    {
        if (!$image instanceof ImagickImage && !$image instanceof GmagickImage) {
            throw new NotSupportedException('ReduceNoiseFilterLoader is only compatible with "imagick" and "gmagick" drivers');
        }

        if (!empty($options)) {
            $this->filter->setOption('radius', $options[0]);
        }

        return $this->filter->apply($image);
    }
}
