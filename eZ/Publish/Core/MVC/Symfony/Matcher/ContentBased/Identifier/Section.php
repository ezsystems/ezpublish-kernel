<?php
/**
 * File containing the Section identifier matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Repository;
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
            function ( Repository $repository ) use ( $location )
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
            function ( Repository $repository ) use ( $contentInfo )
            {
                return $repository->getSectionService()->loadSection(
                    $contentInfo->sectionId
                );
            }
        );

        return isset( $this->values[$section->identifier] );
    }
}
