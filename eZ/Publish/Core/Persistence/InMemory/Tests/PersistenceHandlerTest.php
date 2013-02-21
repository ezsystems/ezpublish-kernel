<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\PersistenceHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\InMemory\Tests;

/**
 * Test case for Persistence\Handler using in-memory storage.
 */
class PersistenceHandlerTest extends HandlerTest
{
    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\Handler::__construct
     */
    public function testHandler()
    {
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Handler', $this->persistenceHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\Handler', $this->persistenceHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentHandler::__construct
     */
    public function testContentHandler()
    {
        $handler = $this->persistenceHandler->contentHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SearchHandler::__construct
     */
    public function testSearchHandler()
    {
        $handler = $this->persistenceHandler->searchHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Search\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\SearchHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LanguageHandler::__construct
     */
    public function testLanguageHandler()
    {
        $handler = $this->persistenceHandler->contentLanguageHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\LanguageHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ContentTypeHandler::__construct
     */
    public function testContentTypeHandler()
    {
        $handler = $this->persistenceHandler->contentTypeHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentTypeHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LocationHandler::__construct
     */
    public function testContentLocationHandler()
    {
        $handler = $this->persistenceHandler->locationHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\LocationHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\TrashHandler::__construct
     */
    public function testTrashHandler()
    {
        $handler = $this->persistenceHandler->trashHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Trash\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\TrashHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\ObjectStateHandler::__construct
     */
    public function testObjectStateHandler()
    {
        $handler = $this->persistenceHandler->objectStateHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ObjectStateHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::__construct
     */
    public function testSectionHandler()
    {
        $handler = $this->persistenceHandler->sectionHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\SectionHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\UserHandler::__construct
     */
    public function testUserHandler()
    {
        $handler = $this->persistenceHandler->userHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UserHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\UrlAliasHandler::__construct
     */
    public function testUrlAliasHandler()
    {
        $handler = $this->persistenceHandler->urlAliasHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UrlAliasHandler', $handler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\UrlWildcardHandler::__construct
     */
    public function testUrlWildcardHandler()
    {
        $handler = $this->persistenceHandler->urlWildcardHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UrlWildcard\\Handler', $handler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UrlWildcardHandler', $handler );
    }
}
