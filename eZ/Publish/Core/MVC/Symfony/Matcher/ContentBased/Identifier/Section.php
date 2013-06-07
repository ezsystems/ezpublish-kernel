<?php
/**
 * File containing the Section identifier matcher class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

class Section extends MultipleValued
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
        $section = $this->repository->sudo(
            function ( $repository ) use ( $location )
            {
                return $repository->getSectionService()->loadSection(
                    $location->getContentInfo()->sectionId
                );
            }
        );

        return isset( $this->values[$section->identifier] );
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
        $section = $this->repository->sudo(
            function ( $repository ) use ( $contentInfo )
            {
                return $repository->getSectionService()->loadSection(
                    $contentInfo->sectionId
                );
            }
        );

        return isset( $this->values[$section->identifier] );
    }
}
