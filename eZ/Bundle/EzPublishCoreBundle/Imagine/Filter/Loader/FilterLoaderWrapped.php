<?php

/**
 * File containing the RelativeScaleFilterLoad class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\Loader;

use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

abstract class FilterLoaderWrapped implements LoaderInterface
{
    /** @var LoaderInterface */
    protected $innerLoader;

    /**
     * @param LoaderInterface $innerLoader
     */
    public function setInnerLoader(LoaderInterface $innerLoader)
    {
        $this->innerLoader = $innerLoader;
    }
}
