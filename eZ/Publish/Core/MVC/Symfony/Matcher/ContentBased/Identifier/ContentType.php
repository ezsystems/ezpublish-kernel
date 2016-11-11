<?php

/**
 * File containing the ContentType matcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\Identifier;

use eZ\Publish\Core\MVC\Symfony\Matcher\ContentBased\MultipleValued;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use eZ\Publish\Core\MVC\Symfony\View\View;

class ContentType extends MultipleValued
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
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($location->getContentInfo()->contentTypeId);

        return isset($this->values[$contentType->identifier]);
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
        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($contentInfo->contentTypeId);

        return isset($this->values[$contentType->identifier]);
    }

    public function match(View $view)
    {
        if (!$view instanceof ContentValueView) {
            return false;
        }

        $contentType = $this->repository
            ->getContentTypeService()
            ->loadContentType($view->getContent()->contentInfo->contentTypeId);

        return isset($this->values[$contentType->identifier]);
    }
}
