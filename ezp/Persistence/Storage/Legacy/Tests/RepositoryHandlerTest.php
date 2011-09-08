<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\RepositoryHandler;

/**
 * Test case for Repository Handler
 */
class RepositoryHandlerTest extends TestCase
{
    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::contentHandler
     * @return void
     */
    public function testContentHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $contentHandler = $handler->contentHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Handler',
            $contentHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Handler',
            $contentHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::contentHandler
     * @return void
     */
    public function testContentHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->contentHandler(),
            $handler->contentHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::getFieldValueConverterRegistry
     * @return void
     */
    public function testGetFieldValueConverterRegistry()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $registry = $handler->getFieldValueConverterRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::getFieldValueConverterRegistry
     * @return void
     */
    public function testGetFieldValueConverterRegistryTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->getFieldValueConverterRegistry(),
            $handler->getFieldValueConverterRegistry()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::getStorageRegistry
     * @return void
     */
    public function testGetStorageRegistry()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $registry = $handler->getStorageRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageRegistry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::getStorageRegistry
     * @return void
     */
    public function testGetStorageRegistryTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->getStorageRegistry(),
            $handler->getStorageRegistry()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::searchHandler
     * @return void
     */
    public function testSearchHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $searchHandler = $handler->searchHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Search\\Handler',
            $searchHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Search\\Handler',
            $searchHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::searchHandler
     * @return void
     */
    public function testSearchHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->searchHandler(),
            $handler->searchHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::contentTypeHandler
     * @return void
     */
    public function testContentTypeHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $contentTypeHandler = $handler->contentTypeHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Type\\Handler',
            $contentTypeHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Handler',
            $contentTypeHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::contentLanguageHandler
     * @return void
     */
    public function testContentLanguageHandler()
    {
        $this->markTestSkipped( 'Not testable due to broken DI.' );
        $handler = $this->getRepositoryHandlerFixture();
        $contentLanguageHandler = $handler->contentLanguageHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Language\\Handler',
            $contentLanguageHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Language\\CachingHandler',
            $contentLanguageHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::contentTypeHandler
     * @return void
     */
    public function testContentTypeHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->contentTypeHandler(),
            $handler->contentTypeHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::locationHandler
     * @return void
     */
    public function testLocationHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $locationHandler = $handler->locationHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Location\\Handler',
            $locationHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Location\\Handler',
            $locationHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::locationHandler
     * @return void
     */
    public function testLocationHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->locationHandler(),
            $handler->locationHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::userHandler
     * @return void
     */
    public function testUserHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $userHandler = $handler->userHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\User\\Handler',
            $userHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\User\\Handler',
            $userHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::userHandler
     * @return void
     */
    public function testUserHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->userHandler(),
            $handler->userHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::sectionHandler
     * @return void
     */
    public function testSectionHandler()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $sectionHandler = $handler->sectionHandler();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content\\Section\\Handler',
            $sectionHandler
        );
        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\Section\\Handler',
            $sectionHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\RepositoryHandler::sectionHandler
     * @return void
     */
    public function testSectionHandlerTwice()
    {
        $handler = $this->getRepositoryHandlerFixture();

        $this->assertSame(
            $handler->sectionHandler(),
            $handler->sectionHandler()
        );
    }

    /**
     * Returns the RepositoryHandler
     *
     * @return RepositoryHandler
     */
    protected function getRepositoryHandlerFixture()
    {
        return new RepositoryHandler( $this->getDsn() );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
