<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

/**
 * Interface for indexing in search backend.
 */
interface Indexing
{
    /**
     * Deletes a content object from the index.
     *
     * @param string|int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null);

    /**
     * Deletes a location from the index.
     *
     * @param string|int $locationId
     * @param string|int $contentId
     */
    public function deleteLocation($locationId, $contentId);
}
