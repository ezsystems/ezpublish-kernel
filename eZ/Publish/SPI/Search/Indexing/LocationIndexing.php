<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\Indexing;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Search\Indexing;

/**
 * Interface for indexing Locations in the search backend.
 */
interface LocationIndexing extends Indexing
{
    /**
     * Indexes a Location in the index storage.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function indexLocation(Location $location);
}
