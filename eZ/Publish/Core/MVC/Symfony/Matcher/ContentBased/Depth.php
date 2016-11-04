<?php

/**
 * File containing the Depth matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class Depth extends MultipleValued
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
        return isset($this->values[$location->depth]);
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
        $location = $this->repository->sudo(
            function (Repository $repository) use ($contentInfo) {
                return $repository->getLocationService()->loadLocation($contentInfo->mainLocationId);
            }
        );

        return isset($this->values[$location->depth]);
    }

    public function match(View $view)
    {
        if ($view instanceof LocationValueView) {
            return isset($this->values[$view->getLocation()->depth]);
        }

        if ($view instanceof ContentValueView) {
            return $this->matchContentInfo($view->getContent()->contentInfo);
        }

        return false;
    }
}
