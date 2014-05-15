<?php
/**
 * File containing the Depth matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class Depth extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return boolean
     */
    public function matchLocation( Location $location )
    {
        return isset( $this->values[$location->depth] );
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
        return isset( $this->values[$location->depth] );
    }
}
