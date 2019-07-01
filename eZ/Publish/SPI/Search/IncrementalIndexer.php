<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Search;

/**
 * IncrementalIndexer contracts.
 *
 * These contracts aims to split re-indexing into tree tasks, to provide smoother operation in production:
 * - Remove items in index no longer valid in database
 * - Making purge of index optional
 * - indexing by specifying id's, for purpose of supporting parallel indexing
 */
interface IncrementalIndexer
{
    /**
     * Update search engine index based on Content id's.
     *
     * If content is:
     * - deleted (NotFoundException)
     * - not published (draft or trashed)
     * Then item is removed from index, if not it is added/updated.
     *
     * @param int[] $contentIds
     * @param bool $commit
     */
    public function updateSearchIndex(array $contentIds, $commit);

    /**
     * Purge whole index, should only be done if user asked for it.
     */
    public function purge();

    /**
     * Return human readable name of given search engine (and if custom indexer you can append that to).
     *
     * @return string
     */
    public function getName();
}
