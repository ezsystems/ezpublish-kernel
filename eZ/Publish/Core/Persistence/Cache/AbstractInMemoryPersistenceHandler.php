<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Adapter\TransactionAwareAdapterInterface;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Ibexa\Core\Persistence\Cache\Tag\CacheIdentifierGeneratorInterface;

/**
 * Internal abstract handler for use in other SPI Persistence Cache Handlers.
 *
 * @internal Only for use as abstract in eZ\Publish\Core\Persistence\Cache\*Handlers.
 */
abstract class AbstractInMemoryPersistenceHandler extends AbstractInMemoryHandler
{
    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    /** @var \Ibexa\Core\Persistence\Cache\Tag\CacheIdentifierGeneratorInterface */
    protected $cacheIdentifierGenerator;

    /**
     * Setups current handler with everything needed.
     *
     * @param \eZ\Publish\Core\Persistence\Cache\Adapter\TransactionAwareAdapterInterface $cache
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     * @param \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache $inMemory
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \Ibexa\Core\Persistence\Cache\Tag\CacheIdentifierGeneratorInterface $cacheIdentifierGenerator
     */
    public function __construct(
        TransactionAwareAdapterInterface $cache,
        PersistenceLogger $logger,
        InMemoryCache $inMemory,
        PersistenceHandler $persistenceHandler,
        CacheIdentifierGeneratorInterface $cacheIdentifierGenerator
    ) {
        parent::__construct($cache, $logger, $inMemory);

        $this->persistenceHandler = $persistenceHandler;
        $this->cacheIdentifierGenerator = $cacheIdentifierGenerator;

        $this->init();
    }

    /**
     * Optional function to initialize handler without having to overload __construct().
     */
    protected function init(): void
    {
        // overload to add init logic if needed in handler
    }
}
