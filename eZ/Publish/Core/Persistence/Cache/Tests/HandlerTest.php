<?php
/**
 * File contains Test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\Core\Persistence\Cache\Handler as CacheHandler;
use eZ\Publish\Core\Persistence\Cache\SectionHandler as CacheSectionHandler;
use eZ\Publish\Core\Persistence\Cache\LocationHandler as CacheLocationHandler;
use eZ\Publish\Core\Persistence\Cache\ContentHandler as CacheContentHandler;
use eZ\Publish\Core\Persistence\Cache\ContentLanguageHandler as CacheContentLanguageHandler;
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use eZ\Publish\Core\Persistence\Cache\UserHandler as CacheUserHandler;
use eZ\Publish\Core\Persistence\Cache\SearchHandler as CacheSearchHandler;
use eZ\Publish\Core\Persistence\Cache\UrlAliasHandler as CacheUrlAliasHandler;
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
     * @var \eZ\Publish\Core\Persistence\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistenceFactoryMock;

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

        $this->persistenceFactoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Persistence\\Factory",
            $this->persistenceFactoryMockMethods,
            array(),
            '',
            false
        );

        $this->cacheMock = $this->getMock(
            "Tedivm\\StashBundle\\Service\\CacheService",
            array(),
            array(),
            '',
            false
        );

        $this->loggerMock = $this->getMock( "eZ\\Publish\\Core\\Persistence\\Cache\\PersistenceLogger" );

        $this->persistenceHandler = new CacheHandler(
            $this->persistenceFactoryMock,
            new CacheSectionHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheLocationHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheContentHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheContentLanguageHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheContentTypeHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheUserHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheSearchHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            new CacheUrlAliasHandler( $this->cacheMock, $this->persistenceFactoryMock, $this->loggerMock ),
            $this->loggerMock
        );
    }

    /**
     * Tear down test (properties)
     */
    protected function tearDown()
    {
        unset( $this->cacheMock );
        unset( $this->persistenceFactoryMock );
        unset( $this->persistenceHandler );
        unset( $this->loggerMock );
        parent::tearDown();
    }
}
