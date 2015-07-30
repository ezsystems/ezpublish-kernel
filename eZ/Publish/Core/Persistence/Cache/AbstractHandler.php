<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * Class AbstractHandler.
 *
 * Abstract handler for use in other Persistence Cache Handlers.
 */
abstract class AbstractHandler
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator
     */
    protected $cache;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * Setups current handler with everything needed.
     *
     * @param \eZ\Publish\Core\Persistence\Cache\CacheServiceDecorator $cache
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct(
        CacheServiceDecorator $cache,
        PersistenceHandler $persistenceHandler,
        PersistenceLogger $logger
    ) {
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->logger = $logger;
    }
}
