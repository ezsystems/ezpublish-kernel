<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search;

/**
 * Interface for creating a search index in search handler.
 */
interface Indexing
{
    /**
     * Create search engine index.
     *
     * @param $bulkCount
     * @param \eZ\Publish\SPI\Search\IndexerDataProvider $dataProvider
     * @param callable $onOutput
     * @param callable $onBatchStarted
     * @param callable $onBatchFinished
     * @param callable $onBulkProcessed
     * @param callable $onError
     */
    public function createSearchIndex(
        $bulkCount,
        IndexerDataProvider $dataProvider,
        callable $onOutput,
        callable $onBatchStarted,
        callable $onBatchFinished,
        callable $onBulkProcessed,
        callable $onError
    );
}
