<?php
/**
 * File containing the ParentContentType Identifier matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ParentContentType extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return boolean
     */
    public function matchLocation( APILocation $location )
    {
        $parentContentType = $this->repository->sudo(
            function ( $repository ) use ( $location )
            {
                $parent = $repository->getLocationService()->loadLocation( $location->parentLocationId );
                return $repository
                    ->getContentTypeService()
                    ->loadContentType( $parent->getContentInfo()->contentTypeId );
            }
        );

        return isset( $this->values[$parentContentType->identifier] );
    }

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return boolean
     */
    public function matchContentInfo( ContentInfo $contentInfo )
    {
        $location = $this->repository->sudo(
            function ( $repository ) use ( $contentInfo )
            {
                return $repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
            }
        );
        return $this->matchLocation( $location );
    }
}
