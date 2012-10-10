<?php
/**
 * File containing the ParentContentType Identifier matcher class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued,
    eZ\Publish\API\Repository\Values\Content\Location as APILocation,
    eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ParentContentType extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return bool
     */
    public function matchLocation( APILocation $location )
    {
        $parent = $this->repository->getLocationService()->loadLocation( $location->parentLocationId );
        $parentContentType = $parent->getContentInfo()->getContentType();
        return isset( $this->values[$parentContentType->identifier] );
    }

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @return bool
     */
    public function matchContentInfo( ContentInfo $contentInfo )
    {
        return $this->matchLocation(
            $this->repository->getLocationService()->loadLocation( $contentInfo->mainLocationId )
        );
    }
}
