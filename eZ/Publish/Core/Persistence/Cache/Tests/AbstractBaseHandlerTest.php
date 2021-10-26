<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Cache\Adapter\TransactionalInMemoryCacheAdapter;
use eZ\Publish\Core\Persistence\Cache\Handler as CacheHandler;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\TransactionHandler as CacheTransactionHandler;
use eZ\Publish\Core\Persistence\Cache\TrashHandler as CacheTrashHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
use eZ\Publish\Core\Persistence\Cache\ObjectStateHandler as CacheObjectStateHandler;
use eZ\Publish\Core\Persistence\Cache\URLHandler as CacheUrlHandler;
use eZ\Publish\Core\Persistence\Cache\BookmarkHandler as CacheBookmarkHandler;
use eZ\Publish\Core\Persistence\Cache\NotificationHandler as CacheNotificationHandler;
use eZ\Publish\Core\Persistence\Cache\UserPreferenceHandler as CacheUserPreferenceHandler;
use eZ\Publish\Core\Persistence\Cache\UrlWildcardHandler as CacheUrlWildcardHandler;
use eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use eZ\Publish\SPI\Persistence\Handler;
use Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer;
use Ibexa\Core\Persistence\Cache\LocationPathConverter;
use Symfony\Component\Cache\CacheItem;
use PHPUnit\Framework\TestCase;

/**
 * Abstract test case for spi cache impl.
 */
abstract class AbstractBaseHandlerTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Cache\Adapter\TransactionalInMemoryCacheAdapter|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheMock;

    /** @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit\Framework\MockObject\MockObject */
    protected $persistenceHandlerMock;

    /** @var \eZ\Publish\Core\Persistence\Cache\Handler */
    protected $persistenceCacheHandler;

    /** @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger|\PHPUnit\Framework\MockObject\MockObject */
    protected $loggerMock;

    /** @var \eZ\Publish\Core\Persistence\Cache\InMemory\InMemoryCache|\PHPUnit\Framework\MockObject\MockObject */
    protected $inMemoryMock;

    /** @var \Closure */
    protected $cacheItemsClosure;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheIdentifierGeneratorMock;

    /** @var \Ibexa\Core\Persistence\Cache\Identifier\CacheIdentifierSanitizer */
    protected $cacheIdentifierSanitizer;

    /** @var \Ibexa\Core\Persistence\Cache\LocationPathConverter */
    protected $locationPathConverter;

    /**
     * Setup the HandlerTest.
     */
    final protected function setUp()
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->createMock(Handler::class);
        $this->cacheMock = $this->createMock(TransactionalInMemoryCacheAdapter::class);
        $this->loggerMock = $this->createMock(PersistenceLogger::class);
        $this->inMemoryMock = $this->createMock(InMemoryCache::class);
        $this->cacheIdentifierGeneratorMock = $this->createMock(CacheIdentifierGeneratorInterface::class);
        $this->cacheIdentifierSanitizer = new CacheIdentifierSanitizer();
        $this->locationPathConverter = new LocationPathConverter();

        $cacheAbstractHandlerArguments = $this->provideAbstractCacheHandlerArguments();
        $cacheInMemoryHandlerArguments = $this->provideInMemoryCacheHandlerArguments();

        $this->persistenceCacheHandler = new CacheHandler(
            $this->persistenceHandlerMock,
            new CacheSectionHandler(...$cacheAbstractHandlerArguments),
            new CacheLocationHandler(...$cacheInMemoryHandlerArguments),
            new CacheContentHandler(...$cacheInMemoryHandlerArguments),
            new CacheContentLanguageHandler(...$cacheInMemoryHandlerArguments),
            new CacheContentTypeHandler(...$cacheInMemoryHandlerArguments),
            new CacheUserHandler(...$cacheInMemoryHandlerArguments),
            new CacheTransactionHandler(...$cacheInMemoryHandlerArguments),
            new CacheTrashHandler(...$cacheAbstractHandlerArguments),
            new CacheUrlAliasHandler(...$cacheInMemoryHandlerArguments),
            new CacheObjectStateHandler(...$cacheInMemoryHandlerArguments),
            new CacheUrlHandler(...$cacheAbstractHandlerArguments),
            new CacheBookmarkHandler(...$cacheAbstractHandlerArguments),
            new CacheNotificationHandler(...$cacheAbstractHandlerArguments),
            new CacheUserPreferenceHandler(...$cacheInMemoryHandlerArguments),
            new CacheUrlWildcardHandler(...$cacheAbstractHandlerArguments),
            $this->loggerMock
        );

        $this->cacheItemsClosure = \Closure::bind(
            static function ($key, $value, $isHit, $defaultLifetime = 0) {
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
        unset(
            $this->cacheMock,
            $this->persistenceHandlerMock,
            $this->persistenceCacheHandler,
            $this->loggerMock,
            $this->cacheItemsClosure,
            $this->inMemoryMock,
            $this->cacheIdentifierGeneratorMock,
            $this->cacheIdentifierSanitizer,
            $this->locationPathConverter
        );

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

    private function provideAbstractCacheHandlerArguments(): array
    {
        return [
            $this->cacheMock,
            $this->persistenceHandlerMock,
            $this->loggerMock,
            $this->cacheIdentifierGeneratorMock,
            $this->cacheIdentifierSanitizer,
            $this->locationPathConverter,
        ];
    }

    private function provideInMemoryCacheHandlerArguments(): array
    {
        return [
            $this->cacheMock,
            $this->loggerMock,
            $this->inMemoryMock,
            $this->persistenceHandlerMock,
            $this->cacheIdentifierGeneratorMock,
            $this->cacheIdentifierSanitizer,
            $this->locationPathConverter,
        ];
    }
}
