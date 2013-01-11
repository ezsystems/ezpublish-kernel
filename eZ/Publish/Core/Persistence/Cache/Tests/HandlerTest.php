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
use eZ\Publish\Core\Persistence\Cache\ContentTypeHandler as CacheContentTypeHandler;
use PHPUnit_Framework_TestCase;

/**
 * Abstract test case for spi cache impl
 */
abstract class HandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Stash\Pool|\PHPUnit_Framework_MockObject_MockObject
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
     * Setup the HandlerTest.
     *
     * @param array|null $persistenceFactoryMockMethods
     */
    protected function setUp( $persistenceFactoryMockMethods = array() )
    {
        parent::setUp();

        $this->persistenceFactoryMock = $this->getMock(
            "eZ\\Publish\\Core\\Persistence\\Factory",
            $persistenceFactoryMockMethods,
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

        $this->persistenceHandler = new CacheHandler(
            $this->persistenceFactoryMock,
            new CacheSectionHandler( $this->cacheMock, $this->persistenceFactoryMock ),
            new CacheLocationHandler( $this->cacheMock, $this->persistenceFactoryMock ),
            new CacheContentTypeHandler( $this->cacheMock, $this->persistenceFactoryMock )
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
        parent::tearDown();
    }
}
