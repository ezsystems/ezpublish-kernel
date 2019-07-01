<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Search\IncrementalIndexer as SPIIncrementalIndexer;
use Psr\Log\LoggerInterface;

/**
 * @deprecated since 8.0, implement \eZ\Publish\SPI\Search\IncrementalIndexer instead.
 *
 * @see \eZ\Publish\SPI\Search\IncrementalIndexer
 */
abstract class IncrementalIndexer implements SPIIncrementalIndexer
{
    /**
     * Updates search engine index based on Content id's.
     *
     * If content is:
     * - deleted (NotFoundException)
     * - not published (draft or trashed)
     * Then item is removed from index, if not it is added/updated.
     *
     * @param int[] $contentIds
     * @param bool $commit
     */
    abstract public function updateSearchIndex(array $contentIds, $commit);

    /**
     * Purges whole index, should only be done if user asked for it.
     */
    abstract public function purge();

    /**
     * Return human readable name of given search engine (and if custom indexer you can append that to).
     *
     * @return string
     */
    abstract public function getName();

    /**
     * IncrementalIndexer constructor for BC reasons.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     */
    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        DatabaseHandler $databaseHandler,
        SearchHandler $searchHandler
    ) {
        $this->logger = $logger;
        $this->persistenceHandler = $persistenceHandler;
        $this->databaseHandler = $databaseHandler;
        $this->searchHandler = $searchHandler;
    }
}
