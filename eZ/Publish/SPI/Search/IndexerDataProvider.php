<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

interface IndexerDataProvider
{
    /**
     * Get a total number of published content objects.
     *
     * @return int
     */
    public function getPublishedContentCount();

    /**
     * Get content objects ids (and version ids) generator.
     *
     * @return \Generator generating an associative array ('id' => ..., 'current_version' => ...)
     */
    public function getContentObjects();

    /**
     * Get the raw data of a content object identified by $id and $version, in a struct.
     *
     * @param int $id
     * @param int $currentVersion version number
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    public function loadContentObjectVersion($id, $currentVersion);

    /**
     * Get a number of nodes in content object tree.
     *
     * @return int
     */
    public function getLocationsCount();

    /**
     * Get location node ids generator.
     *
     * @return \Generator generating node ids (int)
     */
    public function getLocations();

    /**
     * Load the data for the location identified by $locationId.
     *
     * @param int $locationId
     * @return \eZ\Publish\SPI\Persistence\Content\Location
     */
    public function loadLocation($locationId);
}
