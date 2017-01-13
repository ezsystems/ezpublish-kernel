<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Handler', $this->persistenceCacheHandler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\Handler', $this->persistenceCacheHandler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\ContentHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\ContentLanguageHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\ContentTypeHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\LocationHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\TrashHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\ObjectStateHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\SectionHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\User\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\UserHandler', $handler);
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\UrlAliasHandler', $handler);
    }

    /**
     * Test that instance is of correct type.
     *
     * @covers \eZ\Publish\Core\Persistence\Cache\Handler::urlWildcardHandler
     */
    public function testUrlWildcardHandler()
    {
        $this->loggerMock->expects($this->once())->method('logUnCachedHandler');
        $this->persistenceHandlerMock->expects($this->once())->method('urlWildcardHandler');
        $this->persistenceCacheHandler->urlWildcardHandler();
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
        $this->assertInstanceOf('eZ\\Publish\\SPI\\Persistence\\TransactionHandler', $handler);
        $this->assertInstanceOf('eZ\\Publish\\Core\\Persistence\\Cache\\TransactionHandler', $handler);
    }
}
