<?php

/**
 * File containing the Section identifier matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Section extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function matchLocation(Location $location)
    {
        $section = $this->repository->sudo(
            function (Repository $repository) use ($location) {
                return $repository->getSectionService()->loadSection(
                    $location->getContentInfo()->sectionId
                );
            }
        );

        return isset($this->values[$section->identifier]);
    }

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return bool
     */
    public function matchContentInfo(ContentInfo $contentInfo)
    {
        $section = $this->repository->sudo(
            function (Repository $repository) use ($contentInfo) {
                return $repository->getSectionService()->loadSection(
                    $contentInfo->sectionId
                );
            }
        );

        return isset($this->values[$section->identifier]);
    }

    public function match(View $view)
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }

        $contentInfo = $view->getContent()->contentInfo;
        $section = $this->repository->sudo(
            function (Repository $repository) use ($contentInfo) {
                return $repository->getSectionService()->loadSection(
                    $contentInfo->sectionId
                );
            }
        );

        return isset($this->values[$section->identifier]);
    }
}
