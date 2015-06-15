<?php
/**
 * File containing the ParentContentType Id matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Repository;
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
        $parent = $this->repository->sudo(
            function ( Repository $repository ) use ( $location )
            {
                return $repository->getLocationService()->loadLocation( $location->parentLocationId );
            }
        );

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
        $location = $this->repository->sudo(
            function ( Repository $repository ) use ( $contentInfo )
            {
                return $repository->getLocationService()->loadLocation( $contentInfo->mainLocationId );
            }
        );
        return $this->matchLocation( $location );
    }
}
