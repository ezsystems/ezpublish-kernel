<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Cache\Tests\HandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Factory;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Handler using in memory storage.
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceMock;

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->containerMock = $this->getMock( "Symfony\\Component\\DependencyInjection\\ContainerInterface" );
        $this->persistenceFactory = new Factory( $this->containerMock, 'persistence_mock' );

        $this->persistenceMock = $this->getMock( "eZ\\Publish\\SPI\\Persistence\\Handler" );
        $this->containerMock->expects( $this->once() )
            ->method( 'get' )
            ->with( 'persistence_mock' )
            ->will(  $this->returnValue( $this->persistenceMock ) );
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->containerMock );
        unset( $this->persistenceFactory );
        unset( $this->persistenceMock );
        parent::tearDown();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getPersistenceHandler
     */
    public function testGetPersistenceHandler()
    {
        $persistenceHandler = $this->persistenceFactory->getPersistenceHandler();
        $this->assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Handler', $persistenceHandler );
        $this->assertSame( $this->persistenceMock, $persistenceHandler );
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getContentHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetContentHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'contentHandler' );
        $this->persistenceFactory->getContentHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getSearchHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetSearchHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'searchHandler' );
        $this->persistenceFactory->getSearchHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getContentLanguageHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetContentLanguageHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'contentLanguageHandler' );
        $this->persistenceFactory->getContentLanguageHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getContentTypeHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetContentTypeHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'contentTypeHandler' );
        $this->persistenceFactory->getContentTypeHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getLocationHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetContentLocationHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'locationHandler' );
        $this->persistenceFactory->getLocationHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getObjectStateHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetObjectStateHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'objectStateHandler' );
        $this->persistenceFactory->getObjectStateHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getTrashHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetTrashHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'trashHandler' );
        $this->persistenceFactory->getTrashHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getSectionHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetSectionHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'sectionHandler' );
        $this->persistenceFactory->getSectionHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getUserHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetUserHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'userHandler' );
        $this->persistenceFactory->getUserHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getUrlAliasHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetUrlAliasHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'urlAliasHandler' );
        $this->persistenceFactory->getUrlAliasHandler();
    }

    /**
     * Test that instance is of correct type
     *
     * @covers eZ\Publish\Core\Persistence\Factory::getUrlWildcardHandler
     * @depends testGetPersistenceHandler
     */
    public function testGetUrlWildcardHandler()
    {
        $this->persistenceMock->expects( $this->once() )->method( 'urlWildcardHandler' );
        $this->persistenceFactory->getUrlWildcardHandler();
    }
}
