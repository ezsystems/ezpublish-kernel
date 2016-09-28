<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\HttpCache\Controller;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;

abstract class AbstractController
{
    protected function getCacheTagsForContentInfo(ContentInfo $contentInfo)
    {
        return [
            'content' => $contentInfo->id,
            'location' => $contentInfo->mainLocationId,
            'content-type' => $contentInfo->contentTypeId,
        ];
    }

    protected function getCacheTagsForLocation(Location $location)
    {
        return [
            'location' => $location->id,
            'content' => $location->contentInfo->id,
            'content-type' => $location->contentInfo->contentTypeId,
            'path' => $location->path,
        ];
    }
}
