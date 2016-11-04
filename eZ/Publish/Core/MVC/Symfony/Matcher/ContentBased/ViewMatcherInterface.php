<?php

/**
 * File containing the Matcher interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Main interface for content/location matchers.
 */
interface ViewMatcherInterface extends MatcherInterface
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function matchLocation(Location $location);

    /**
     * Checks if a ContentInfo object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return bool
     */
    public function matchContentInfo(ContentInfo $contentInfo);
}
