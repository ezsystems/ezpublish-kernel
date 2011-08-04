<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\RepositoryHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\LegacyStorage\RepositoryHandler;

/**
 * Test case for Repository Handler
 */
class RepositoryHandlerTest extends TestCase
{
    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::contentHandler
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
            'ezp\\Persistence\\LegacyStorage\\Content\\Handler',
            $contentHandler
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::contentHandler
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
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::getFieldValueConverterRegistry
     * @return void
     */
    public function testGetFieldValueConverterRegistry()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $registry = $handler->getFieldValueConverterRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\LegacyStorage\\Content\\FieldValue\\Converter\\Registry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::getFieldValueConverterRegistry
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
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::getStorageRegistry
     * @return void
     */
    public function testGetStorageRegistry()
    {
        $handler = $this->getRepositoryHandlerFixture();
        $registry = $handler->getStorageRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\LegacyStorage\\Content\\StorageRegistry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::getStorageRegistry
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
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::contentTypeHandler
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
            'ezp\\Persistence\\LegacyStorage\\Content\\Type\\Handler',
            $contentTypeHandler
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::contentTypeHandler
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
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::locationHandler
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
            'ezp\\Persistence\\LegacyStorage\\Content\\LocationHandler',
            $locationHandler
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::locationHandler
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
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::userHandler
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
            'ezp\\Persistence\\LegacyStorage\\User\\Handler',
            $userHandler
        );
    }

    /**
     * @covers ezp\Persistence\LegacyStorage\RepositoryHandler::userHandler
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
