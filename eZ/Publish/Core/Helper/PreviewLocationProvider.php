<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\Helper;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as PersistenceLocationHandler;

/**
 * Provides location(s) for a content. Handles unpublished content that does not have an actual location yet.
 */
class PreviewLocationProvider
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler */
    private $locationHandler;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     */
    public function __construct(
        LocationService $locationService,
        ContentService $contentService,
        PersistenceLocationHandler $locationHandler
    ) {
        $this->locationService = $locationService;
        $this->contentService = $contentService;
        $this->locationHandler = $locationHandler;
    }

    /**
     * Loads the main location for $contentId.
     *
     * If the content does not have a location (yet), but has a Location draft, it is returned instead.
     * Location drafts do not have an id (it is set to null), and can be tested using the isDraft() method.
     *
     * If the content doesn't have a location nor a location draft, null is returned.
     *
     * @param mixed $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location|null
     */
    public function loadMainLocation($contentId)
    {
        $location = null;
        $contentInfo = $this->contentService->loadContentInfo($contentId);

        // mainLocationId already exists, content has been published at least once.
        if ($contentInfo->mainLocationId) {
            $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
        } elseif (!$contentInfo->published) {
            // New Content, never published, create a virtual location object.
            // In cases content is missing locations this will return empty array
            $parentLocations = $this->locationHandler->loadParentLocationsForDraftContent($contentInfo->id);
            if (empty($parentLocations)) {
                return null;
            }

            $location = new Location(
                array(
                    'contentInfo' => $contentInfo,
                    'status' => Location::STATUS_DRAFT,
                    'parentLocationId' => $parentLocations[0]->id,
                    'depth' => $parentLocations[0]->depth + 1,
                    'pathString' => $parentLocations[0]->pathString . '/x',
                )
            );
        }

        return $location;
    }
}
