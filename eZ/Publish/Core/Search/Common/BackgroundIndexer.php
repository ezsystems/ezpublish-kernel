<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Interface for performing indexing in background.
 *
 * Example of background: After console command or request has finished execution.
 *
 * NOTE: This is not for use by regular indexing needs reacting to Repository signals, but rather for use inside the
 * Search service when inconsistencies are discovered which should be re-indexed, hence operate as a self healing system.
 */
interface BackgroundIndexer
{
    /**
     * Register a content for refreshing index in the background.
     *
     * If content is:
     * - deleted (NotFoundException)
     * - not published (draft or trashed)
     *
     * .. then item is removed from index, if not it is added/updated.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     */
    public function registerContent(ContentInfo $contentInfo);

    /**
     * Register a location for refreshing index in the background.
     *
     * If content is:
     * - deleted (NotFoundException)
     * - not published (draft or trashed)
     *
     * .. then item is removed from index, if not it is added/updated.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $location
     */
    public function registerLocation(Location $location);
}
