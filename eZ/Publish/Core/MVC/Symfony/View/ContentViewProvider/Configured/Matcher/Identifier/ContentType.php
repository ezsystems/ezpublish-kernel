<?php
/**
 * File containing the ContentType matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\Identifier;

use eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider\Configured\Matcher\MultipleValued;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class ContentType extends MultipleValued
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
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType( $location->getContentInfo()->contentTypeId );

        return isset( $this->values[$contentType->identifier] );
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
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType( $contentInfo->contentTypeId );

        return isset( $this->values[$contentType->identifier] );
    }
}
