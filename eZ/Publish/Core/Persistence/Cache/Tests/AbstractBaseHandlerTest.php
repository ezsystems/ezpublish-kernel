<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Cache\Handler as CacheHandler;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\TransactionHandler as CacheTransactionHandler;
use eZ\Publish\Core\Persistence\Cache\TrashHandler as CacheTrashHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
use eZ\Publish\Core\Persistence\Cache\ObjectStateHandler as CacheObjectStateHandler;
use eZ\Publish\Core\Persistence\Cache\URLHandler as CacheUrlHandler;
use eZ\Publish\Core\Persistence\Cache\BookmarkHandler as CacheBookmarkHandler;
use eZ\Publish\Core\Persistence\Cache\NotificationHandler as CacheNotificationHandler;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for spi cache impl.
 */
abstract class AbstractBaseHandlerTest extends TestCase
{
    /**
     * @var \Symfony\Component\Cache\Adapter\TagAwareAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\Handler
     */
    protected $persistenceCacheHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Closure
     */
    protected $cacheItemsClosure;

    /**
     * Setup the HandlerTest.
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(Handler::class);
        $this->cacheMock = $this->createMock(TagAwareAdapterInterface::class);
        $this->loggerMock = $this->createMock(PersistenceLogger::class);

        $this->persistenceCacheHandler = new CacheHandler(
            $this->persistenceHandlerMock,
            new CacheSectionHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheLocationHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheContentHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheContentLanguageHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheContentTypeHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheUserHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheTransactionHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheTrashHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheUrlAliasHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheObjectStateHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheUrlHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheBookmarkHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            new CacheNotificationHandler($this->cacheMock, $this->persistenceHandlerMock, $this->loggerMock),
            $this->loggerMock
        );

        $this->cacheItemsClosure = \Closure::bind(
            function ($key, $value, $isHit, $defaultLifetime = 0) {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->defaultLifetime = $defaultLifetime;

                return $item;
            },
            null,
            CacheItem::class
        );
    }

    /**
     * Tear down test (properties).
     */
    final protected function tearDown()
    {
        unset($this->cacheMock);
        unset($this->persistenceHandlerMock);
        unset($this->persistenceCacheHandler);
        unset($this->loggerMock);
        unset($this->cacheItemsClosure);
        parent::tearDown();
    }

    /**
     * @param $key
     * @param null $value If null the cache item will be assumed to be a cache miss here.
     * @param int $defaultLifetime
     *
     * @return CacheItem
     */
    final protected function getCacheItem($key, $value = null, $defaultLifetime = 0)
    {
        $cacheItemsClosure = $this->cacheItemsClosure;

        return $cacheItemsClosure($key, $value, (bool)$value, $defaultLifetime);
    }
}
