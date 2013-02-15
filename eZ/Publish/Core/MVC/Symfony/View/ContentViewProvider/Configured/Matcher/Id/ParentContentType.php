<?php
/**
 * File containing the ParentContentType Id matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Id;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued;
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
        $parent = $this->repository->getLocationService()->loadLocation( $location->parentLocationId );
        return isset( $this->values[$parent->getContentInfo()->contentTypeId] );
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
        return $this->matchLocation(
            $this->repository->getLocationService()->loadLocation( $contentInfo->mainLocationId )
        );
    }
}
