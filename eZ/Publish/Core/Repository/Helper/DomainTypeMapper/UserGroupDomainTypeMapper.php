<?php
/**
 * File containing the UserGroupDomainTypeMapper class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Helper\DomainTypeMapper;

use eZ\Publish\Core\Repository\Helper\DomainTypeMapper;
use eZ\Publish\Core\Repository\Values\User\UserGroup;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPILocationHandler;

/**
 * DomainTypeMapper for UserGroup object
 *
 * @internal
 */
class UserGroupDomainTypeMapper implements DomainTypeMapper
{
    /**
     * @var SPILocationHandler
     */
    protected $locationHandler;

    /**
     * @param SPILocationHandler $locationHandler
     */
    public function __construct( SPILocationHandler $locationHandler )
    {
        $this->locationHandler = $locationHandler;
    }

    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param array $contentProperties Main properties for Content
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentObject( SPIContent $spiContent, array $contentProperties )
    {
        $parentId = null;
        if ( $mainLocationId = $spiContent->versionInfo->contentInfo->mainLocationId )
        {
            $mainLocation = $this->locationHandler->load( $mainLocationId );
            $parentLocation = $mainLocation->parentId ? $this->locationHandler->load( $mainLocation->parentId ) : null;
            $parentId = $parentLocation ? $parentLocation->contentId : null;
        }
        return new UserGroup(
            array(
                'parentId' => $parentId,
                'subGroupCount' => 0
            ) + $contentProperties
        );
    }
}
