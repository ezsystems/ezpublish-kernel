<?php

/**
 * File containing the Location Search Handler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Search\Content\Location;

use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * The Location Search Handler interface defines search operations on Location elements in the storage engine.
 */
interface Handler
{
    /**
     * Finds locations for the given $query.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationQuery $query
     * @param array $languageFilter Configuration for specifying prioritized languages query will be performed on.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Search\SearchResult With Location as SearchHit->valueObject
     */
    public function findLocations(LocationQuery $query, array $languageFilter = array());

    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location);

    /**
     * Deletes a Location from the index storage.
     *
     * @param int|string $locationId
     */
    public function deleteLocation($locationId);

    /**
     * Deletes a Content from the index storage.
     *
     * @param $contentId
     */
    public function deleteContent($contentId);
}
