<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\Indexer;

use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Indexer for handlers that index Locations.
 */
interface LocationIndexer
{
    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location);

    /**
     * Deletes a location from the index.
     *
     * @param string|int $locationId
     * @param string|int $contentId
     */
    public function deleteLocation($locationId, $contentId);
}
