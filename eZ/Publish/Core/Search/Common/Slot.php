<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use eZ\Publish\SPI\Search\Indexing;
use eZ\Publish\SPI\Search\Indexing\ContentIndexing;
use eZ\Publish\SPI\Search\Indexing\LocationIndexing;

/**
 * General slot implementation for Search Engines.
 */
abstract class Slot extends BaseSlot
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var \eZ\Publish\SPI\Search\Handler
     */
    protected $searchHandler;

    public function __construct(
        Repository $repository,
        PersistenceHandler $persistenceHandler,
        SearchHandler $searchHandler
    ) {
        $this->repository = $repository;
        $this->persistenceHandler = $persistenceHandler;
        $this->searchHandler = $searchHandler;
    }

    /**
     * Returns boolean indicating if the search engine can index Content or Location.
     *
     * To be handled in the concrete implementation.
     *
     * @return bool
     */
    protected function canIndex()
    {
        return $this->searchHandler instanceof Indexing;
    }

    /**
     * Returns boolean indicating if the search handler can index Content.
     *
     * To be handled in the concrete implementation.
     *
     * @return bool
     */
    protected function canIndexContent()
    {
        return $this->searchHandler instanceof ContentIndexing;
    }

    /**
     * Returns boolean indicating if the search handler can index Location.
     *
     * To be handled in the concrete implementation.
     *
     * @return bool
     */
    protected function canIndexLocation()
    {
        return $this->searchHandler instanceof LocationIndexing;
    }
}
