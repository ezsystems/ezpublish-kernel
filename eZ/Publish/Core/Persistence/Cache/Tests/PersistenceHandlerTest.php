<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence as SPIPersistence;
use eZ\Publish\Core\Persistence\Cache;

/**
 * Test case for Persistence\Cache\Handler.
 */
class PersistenceHandlerTest extends AbstractBaseHandlerTest
{
    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::__construct
     */
    public function testHandler()
    {
        $this->assertInstanceOf(SPIPersistence\Handler::class, $this->persistenceCacheHandler);
        $this->assertInstanceOf(Cache\Handler::class, $this->persistenceCacheHandler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::contentHandler
     */
    public function testContentHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->contentHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Handler::class, $handler);
        $this->assertInstanceOf(Cache\ContentHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::contentLanguageHandler
     */
    public function testLanguageHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->contentLanguageHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Language\Handler::class, $handler);
        $this->assertInstanceOf(Cache\ContentLanguageHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::contentTypeHandler
     */
    public function testContentTypeHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->contentTypeHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Type\Handler::class, $handler);
        $this->assertInstanceOf(Cache\ContentTypeHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::locationHandler
     */
    public function testContentLocationHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->locationHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Location\Handler::class, $handler);
        $this->assertInstanceOf(Cache\LocationHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::trashHandler
     */
    public function testTrashHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->trashHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Location\Trash\Handler::class, $handler);
        $this->assertInstanceOf(Cache\TrashHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::objectStateHandler
     */
    public function testObjectStateHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->objectStateHandler();
        $this->assertInstanceOf(SPIPersistence\Content\ObjectState\Handler::class, $handler);
        $this->assertInstanceOf(Cache\ObjectStateHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::sectionHandler
     */
    public function testSectionHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->sectionHandler();
        $this->assertInstanceOf(SPIPersistence\Content\Section\Handler::class, $handler);
        $this->assertInstanceOf(Cache\SectionHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::userHandler
     */
    public function testUserHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->userHandler();
        $this->assertInstanceOf(SPIPersistence\User\Handler::class, $handler);
        $this->assertInstanceOf(Cache\UserHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::urlAliasHandler
     */
    public function testUrlAliasHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->urlAliasHandler();
        $this->assertInstanceOf(SPIPersistence\Content\UrlAlias\Handler::class, $handler);
        $this->assertInstanceOf(Cache\UrlAliasHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::urlWildcardHandler
     */
    public function testUrlWildcardHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->urlWildcardHandler();
        $this->assertInstanceOf(SPIPersistence\Content\UrlWildcard\Handler::class, $handler);
        $this->assertInstanceOf(Cache\UrlWildcardHandler::class, $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::transactionHandler
     */
    public function testTransactionHandler()
    {
        $this->loggerMock->expects($this->never())->method($this->anything());
        $handler = $this->persistenceCacheHandler->transactionHandler();
        $this->assertInstanceOf(SPIPersistence\TransactionHandler::class, $handler);
        $this->assertInstanceOf(Cache\TransactionHandler::class, $handler);
    }
}
