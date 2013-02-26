<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\InMemory\Handler as InMemoryHandler;

/**
 * Test case for Persistence\Cache\Handler
 */
class PersistenceHandlerTest extends HandlerTest
{
    /**
     * @param array|null $persistenceFactoryMockMethod
     */
    protected $persistenceFactoryMockMethods = array( 'getPersistenceHandler' );

    /**
     * Setup the PersistenceHandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->persistenceFactoryMock
            ->expects( $this->any() )
            ->method( 'getPersistenceHandler' )
            ->will( $this->returnValue( new InMemoryHandler() ) );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::__construct
     */
    public function testHandler()
    {
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Handler', $this->persistenceHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\Handler', $this->persistenceHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::contentHandler
     */
    public function testContentHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->contentHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\ContentHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::searchHandler
     */
    public function testSearchHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->searchHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Search\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\SearchHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::contentLanguageHandler
     */
    public function testLanguageHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->contentLanguageHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\ContentLanguageHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::contentTypeHandler
     */
    public function testContentTypeHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->contentTypeHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\ContentTypeHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::locationHandler
     */
    public function testContentLocationHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->locationHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\LocationHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::trashHandler
     */
    public function testTrashHandler()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logUnCachedHandler' );
        $handler = $this->persistenceHandler->trashHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\TrashHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::objectStateHandler
     */
    public function testObjectStateHandler()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logUnCachedHandler' );
        $handler = $this->persistenceHandler->objectStateHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ObjectStateHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::sectionHandler
     */
    public function testSectionHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->sectionHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\SectionHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::userHandler
     */
    public function testUserHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->userHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\UserHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::urlAliasHandler
     */
    public function testUrlAliasHandler()
    {
        $this->loggerMock->expects( $this->never() )->method( $this->anything() );
        $handler = $this->persistenceHandler->urlAliasHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\Cache\\UrlAliasHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Cache\Handler::urlWildcardHandler
     */
    public function testUrlWildcardHandler()
    {
        $this->loggerMock->expects( $this->once() )->method( 'logUnCachedHandler' );
        $handler = $this->persistenceHandler->urlWildcardHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlWildcard\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UrlWildcardHandler', $handler );
    }
}
