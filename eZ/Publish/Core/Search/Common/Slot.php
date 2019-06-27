<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

/**
 * General slot implementation for Search Engines.
 */
abstract class Slot extends BaseSlot
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    /** @var \eZ\Publish\SPI\Search\Handler */
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
}
