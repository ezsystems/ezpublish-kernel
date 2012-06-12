<?php
/**
 * File contains: eZ\Publish\Core\Persistence\InMemory\Tests\PersistenceHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
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
        $contentHandler = $this->persistenceHandler->contentHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Handler', $contentHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentHandler', $contentHandler );
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
        $contentHandler = $this->persistenceHandler->contentTypeHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Type\\Handler', $contentHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\ContentTypeHandler', $contentHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\LocationHandler::__construct
     */
    public function testContentLocationHandler()
    {
        $contentHandler = $this->persistenceHandler->locationHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\Handler', $contentHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\LocationHandler', $contentHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::__construct
     */
    public function testSectionHandler()
    {
        $sectionHandler = $this->persistenceHandler->sectionHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Section\\Handler', $sectionHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\SectionHandler', $sectionHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\InMemory\SectionHandler::__construct
     */
    public function testUserHandler()
    {
        $sectionHandler = $this->persistenceHandler->userHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\User\\Handler', $sectionHandler );
        $this->assertInstanceOf( 'eZ\\Publish\\Core\\Persistence\\InMemory\\UserHandler', $sectionHandler );
    }
}
