<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Cache\Handler as CacheHandler;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\LocationSearchHandler as CacheLocationSearchHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\SearchHandler as CacheSearchHandler;
use eZ\Publish\Core\Persistence\Cache\TrashHandler as CacheTrashHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
use eZ\Publish\Core\Persistence\Factory as PersistenceFactory;
use PHPUnit_Framework_TestCase;

/**
 * Abstract test case for spi cache impl
 */
abstract class HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Tedivm\StashBundle\Service\CacheService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Factory
     */
    protected $persistenceFactory;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $innerPersistenceHandlerMock;

    /**
     * @var \eZ\Publish\Core\Persistence\Cache\Handler
     */
    protected $persistenceHandler;

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

        $this->innerPersistenceHandlerMock = $this->getMock( 'eZ\Publish\SPI\Persistence\Handler' );
        $this->persistenceFactory = new PersistenceFactory( $this->innerPersistenceHandlerMock );

        $this->cacheMock = $this->getMock(
            "eZ\\Publish\\Core\\Persistence\\Cache\\CacheServiceDecorator",
            array(),
            array(),
            '',
            false
        );

        $this->loggerMock = $this->getMock( "eZ\\Publish\\Core\\Persistence\\Cache\\PersistenceLogger" );

        $this->persistenceHandler = new CacheHandler(
            $this->persistenceFactory,
            new CacheSectionHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheLocationHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheContentHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheContentLanguageHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheContentTypeHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheUserHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheSearchHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheTrashHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheLocationSearchHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            new CacheUrlAliasHandler( $this->cacheMock, $this->persistenceFactory, $this->loggerMock ),
            $this->loggerMock,
            $this->cacheMock
        );
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->cacheMock );
        unset( $this->persistenceFactory );
        unset( $this->persistenceHandler );
        unset( $this->loggerMock );
        parent::tearDown();
    }
}
