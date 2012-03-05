<?php
/**
 * File contains: ezp\Content\Tests\LocationProxyTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Proxy as ProxyContent,
    ezp\Content\Location\Proxy as ProxyLocation,
    ezp\Base\Configuration,
    ezp\Base\ServiceContainer,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Content class
 *
 */
class LocationProxyTest extends PHPUnit_Framework_TestCase
{

    /**
     * Repository
     *
     * @var \ezp\Base\Repository
     */
    protected $repository;

    /**
     * Content service
     *
     * @var \ezp\Content\Location\Service
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $sc = new ServiceContainer(
            Configuration::getInstance('service')->getAll(),
            array(
                '@persistence_handler' => new \eZ\Publish\SPI\Persistence\Storage\InMemory\Handler(),
                '@io_handler' => new \ezp\Io\Storage\InMemory(),
            )
        );
        $this->repository = $sc->getRepository();
        $this->service = $this->repository->getLocationService();
    }

    /**
     * Tests Content\Location\Proxy creation
     *
     * @covers \ezp\Content\Location\Proxy::__construct
     */
    public function testProxyConstruct()
    {
        $this->assertInstanceOf( "ezp\\Content\\Location", new ProxyLocation( 1, $this->service ) );
    }

    /**
     * Tests retrieving parent on a Proxy Location
     *
     * @covers \ezp\Content\Location\Proxy::getParent
     */
    public function testProxyGetParent()
    {
        $location = new ProxyLocation( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Location", $location->getParent() );
    }

    /**
     * Tests setting parent on a Proxy Location
     *
     * @covers \ezp\Content\Location\Proxy::setParent
     */
    public function testProxySetParent()
    {
        $this->repository->setUser( $this->repository->getUserService()->load( 14 ) );// "Login" admin
        $location = new ProxyLocation( 5, $this->service );
        $location->setParent( new ProxyLocation( 2, $this->service ) );
    }

    /**
     * Tests retrieving content on a Proxy Location
     *
     * @covers \ezp\Content\Location\Proxy::getContent
     */
    public function testProxyGetContent()
    {
        $location = new ProxyLocation( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content", $location->getContent() );
    }

    /**
     * Tests setting content on a Proxy Location
     *
     * @covers \ezp\Content\Location\Proxy::setContent
     */
    public function testProxySetContent()
    {
        $location = new ProxyLocation( 1, $this->service );
        $location->setContent( new ProxyContent( 10, $this->repository->getContentService() ) );
    }

    /**
     * Tests retrieving versions on a Proxy Location
     *
     * @covers \ezp\Content\Location\Proxy::getChildren
     */
    public function testProxyGetChildren()
    {
        $location = new ProxyLocation( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Lazy", $location->getChildren() );
    }
}
