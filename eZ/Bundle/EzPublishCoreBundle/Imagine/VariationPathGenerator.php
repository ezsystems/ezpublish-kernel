<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Imagine;

/**
 * Generates the path to variations of original images.
 */
interface VariationPathGenerator
{
    /**
     * Returns the variation for image $originalPath with $filter.
     *
     * @param string $originalPath
     * @param string $filter
     *
     * @return string
     */
    public function getVariationPath($originalPath, $filter);
}
