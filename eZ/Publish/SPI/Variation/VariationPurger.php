<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Variation;

/**
 * Handles physical purging of image variations from storage.
 */
interface VariationPurger
{
    /**
     * Purge all variations generated for aliases in $aliasNames.
     *
     * @param array $aliasNames
     */
    public function purge(array $aliasNames);
}
