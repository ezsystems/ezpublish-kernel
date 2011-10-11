<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Handler;

/**
 * Test case for Repository Handler
 */
class HandlerTest extends TestCase
{
    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentHandler
     * @return void
     */
    public function testContentHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentHandler
     * @return void
     */
    public function testContentHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->contentHandler(),
            $handler->contentHandler()
        );
    }

    /**
     * Issue #97
     *
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentHandler
     * @covers ezp\Persistence\Storage\Legacy\Handler::getStorageRegistry
     * @return void
     */
    public function testStorageRegistryReused()
    {
        $handler = $this->getHandlerFixture();

        $storageRegistry = $handler->getStorageRegistry();
        $contentHandler = $handler->contentHandler();
        $fieldHandler = $this->readAttribute(
            $contentHandler,
            'fieldHandler'
        );
        $storageHandler = $this->readAttribute(
            $fieldHandler,
            'storageHandler'
        );

        $this->assertAttributeSame(
            $storageRegistry,
            'storageRegistry',
            $storageHandler
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::getFieldValueConverterRegistry
     * @return void
     */
    public function testGetFieldValueConverterRegistry()
    {
        $handler = $this->getHandlerFixture();
        $registry = $handler->getFieldValueConverterRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\FieldValue\\Converter\\Registry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::getFieldValueConverterRegistry
     * @return void
     */
    public function testGetFieldValueConverterRegistryTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->getFieldValueConverterRegistry(),
            $handler->getFieldValueConverterRegistry()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::getStorageRegistry
     * @return void
     */
    public function testGetStorageRegistry()
    {
        $handler = $this->getHandlerFixture();
        $registry = $handler->getStorageRegistry();

        $this->assertInstanceOf(
            'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageRegistry',
            $registry
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::getStorageRegistry
     * @return void
     */
    public function testGetStorageRegistryTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->getStorageRegistry(),
            $handler->getStorageRegistry()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::searchHandler
     * @return void
     */
    public function testSearchHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::searchHandler
     * @return void
     */
    public function testSearchHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->searchHandler(),
            $handler->searchHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentTypeHandler
     * @return void
     */
    public function testContentTypeHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentLanguageHandler
     * @return void
     */
    public function testContentLanguageHandler()
    {
        $this->markTestSkipped( 'Not testable due to broken DI.' );
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::contentTypeHandler
     * @return void
     */
    public function testContentTypeHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->contentTypeHandler(),
            $handler->contentTypeHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::locationHandler
     * @return void
     */
    public function testLocationHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::locationHandler
     * @return void
     */
    public function testLocationHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->locationHandler(),
            $handler->locationHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::userHandler
     * @return void
     */
    public function testUserHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::userHandler
     * @return void
     */
    public function testUserHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->userHandler(),
            $handler->userHandler()
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::sectionHandler
     * @return void
     */
    public function testSectionHandler()
    {
        $handler = $this->getHandlerFixture();
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
     * @covers ezp\Persistence\Storage\Legacy\Handler::sectionHandler
     * @return void
     */
    public function testSectionHandlerTwice()
    {
        $handler = $this->getHandlerFixture();

        $this->assertSame(
            $handler->sectionHandler(),
            $handler->sectionHandler()
        );
    }

    /**
     * Returns the Handler
     *
     * @return Handler
     */
    protected function getHandlerFixture()
    {
        return new Handler(
            array(
                'dsn' => $this->getDsn(),
            )
        );
    }

    /**
     * @covers ezp\Persistence\Storage\Legacy\Handler::getDatabase
     * @return void
     */
    public function testDatabaseInstance()
    {
        $method = new \ReflectionMethod(
            'ezp\\Persistence\\Storage\\Legacy\\Handler',
            'getDatabase'
        );
        $method->setAccessible( true );

        $dbHandler = $method->invoke( $this->getHandlerFixture() );
        $className = get_class( $this->getDatabaseHandler() );

        $this->assertTrue( $dbHandler instanceof $className, get_class( $dbHandler ) . " not of type $className." );
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
