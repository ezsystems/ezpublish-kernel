<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;

/**
 * Loads a location based on a ContentInfo.
 */
interface ContentInfoLocationLoader
{
    /**
     * Loads a location from a ContentInfo.
     *
     * @param ContentInfo $contentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the location doesn't have a contentId.
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the location failed to load.
     */
    public function loadLocation(ContentInfo $contentInfo);
}
