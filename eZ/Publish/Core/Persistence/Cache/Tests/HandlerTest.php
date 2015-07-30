<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
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
use PHPUnit_Framework_TestCase;

/**
 * Abstract test case for spi cache impl.
 */
abstract class HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Stash\Interfaces\PoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceHandlerMock;

    /**
     * @var \eZ\Publish\SPI\Persistence\TransactionHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionHandlerMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\Handler
     */
    protected $persistenceCacheHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @param array|null $persistenceFactoryMockMethod
     */
    protected $persistenceFactoryMockMethods = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->persistenceHandlerMock = $this->getMock('eZ\Publish\SPI\Persistence\Handler');

        $this->cacheMock = $this->getMock(
            'eZ\\Publish\\Core\\Persistence\\Cache\\CacheServiceDecorator',
            array(),
            array(),
            '',
            false
        );

        $this->loggerMock = $this->getMock('eZ\\Publish\\Core\\Persistence\\Cache\\PersistenceLogger');

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
            $this->loggerMock,
            $this->cacheMock
        );
    }

    /**
     * Tear down test (properties).
     */
    protected function tearDown()
    {
        unset($this->cacheMock);
        unset($this->persistenceHandlerMock);
        unset($this->persistenceCacheHandler);
        unset($this->loggerMock);
        parent::tearDown();
    }
}
