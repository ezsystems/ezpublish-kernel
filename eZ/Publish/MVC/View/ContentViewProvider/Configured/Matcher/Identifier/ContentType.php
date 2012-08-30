<?php
/**
 * File containing the ContentType matcher class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\View\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\MVC\View\ContentViewProvider\Configured\Matcher\MultipleValued,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ContentType extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @return bool
     */
    public function matchLocation( Location $location )
    {
        return isset( $this->values[$location->getContentInfo()->getContentType()->identifier] );
    }

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @return bool
     */
    public function matchContentInfo( ContentInfo $contentInfo )
    {
        return isset( $this->values[$contentInfo->getContentType()->identifier] );
    }
}
