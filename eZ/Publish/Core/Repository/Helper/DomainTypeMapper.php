<?php
/**
 * File containing the DomainTypeMapper interface
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\SPI\Persistence\Content as SPIContent;

/**
 * DomainTypeMapper is an internal interface.
 *
 * This interface allows to customize witch implementation of key content domain objects are used, allowing
 * implementation to add own Domain. Example: User & UserGroup is a custom domain with additional properties.
 *
 * @internal
 */
interface DomainTypeMapper
{
    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param array $contentProperties Main properties for Content
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentObject( SPIContent $spiContent, array $contentProperties );

    /**
     * Builds domain location object from provided persistence location
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $spiLocation
     * @param array $properties Main properties for Location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    //public function buildLocationObject( SPILocation $spiLocation, array $properties );
}
