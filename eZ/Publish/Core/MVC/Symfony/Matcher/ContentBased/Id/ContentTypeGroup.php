<?php

/**
 * File containing the ContentTypeGroup Id matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Id;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class ContentTypeGroup extends MultipleValued
{
    /**
     * Checks if a Location object matches.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     *
     * @return bool
     */
    public function matchLocation(APILocation $location)
    {
        return $this->matchContentTypeId($location->getContentInfo()->contentTypeId);
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
        return $this->matchContentTypeId($contentInfo->contentTypeId);
    }

    public function match(View $view)
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }

        return $this->matchContentTypeId($view->getContent()->contentInfo->contentTypeId);
    }

    /**
     * @return bool
     */
    private function matchContentTypeId($contentTypeId)
    {
        $contentTypeGroups = $this->repository
            ->getContentTypeService()
            ->loadContentType($contentTypeId)
            ->getContentTypeGroups();

        foreach ($contentTypeGroups as $group) {
            if (isset($this->values[$group->id])) {
                return true;
            }
        }

        return false;
    }
}
