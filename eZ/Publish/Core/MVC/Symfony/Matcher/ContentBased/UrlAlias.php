<?php

/**
 * File containing the UrlAlias matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\LocationValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class UrlAlias extends MultipleValued
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
        $urlAliasService = $this->repository->getURLAliasService();
        $locationUrls = array_merge(
            $urlAliasService->listLocationAliases($location),
            $urlAliasService->listLocationAliases($location, false)
        );

        foreach ($this->values as $pattern => $val) {
            foreach ($locationUrls as $urlAlias) {
                if (strpos($urlAlias->path, "/$pattern") === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Not supported since UrlAlias is meaningful for location objects only.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @throws \RuntimeException
     *
     * @return bool
     */
    public function matchContentInfo(ContentInfo $contentInfo)
    {
        throw new \RuntimeException('matchContentInfo() is not supported by UrlAlias matcher');
    }

    public function setMatchingConfig($matchingConfig)
    {
        if (!is_array($matchingConfig)) {
            $matchingConfig = [$matchingConfig];
        }

        array_walk(
            $matchingConfig,
            function (&$item) {
                $item = trim($item, '/ ');
            }
        );

        parent::setMatchingConfig($matchingConfig);
    }

    public function match(View $view)
    {
        if (!$view instanceof LocationValueView) {
            return false;
        }

        return $this->matchLocation($view->getLocation());
    }
}
