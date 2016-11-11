<?php

/**
 * File containing the SwirlFilter class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Gmagick;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\AbstractFilter;
use Imagine\Image\ImageInterface;

class SwirlFilter extends AbstractFilter
{
    /**
     * @param ImageInterface|\Imagine\Gmagick\Image $image
     *
     * @return ImageInterface
     */
    public function apply(ImageInterface $image)
    {
        /** @var \Gmagick $gmagick */
        $gmagick = $image->getGmagick();
        $gmagick->swirlimage((float)$this->getOption('degrees', 60));

        return $image;
    }
}
