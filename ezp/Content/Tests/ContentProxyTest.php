<?php
/**
 * File contains: ezp\Content\Tests\ContentProxyTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Proxy as ProxyContent,
    ezp\Base\Service\Container,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Content class
 *
 */
class ContentProxyTest extends PHPUnit_Framework_TestCase
{

    /**
     * Content service
     *
     * @var \ezp\Content\Service
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $sc = new Container;
        $this->service = $sc->getRepository()->getContentService();
    }

    /**
     * Tests Content\Proxy creation
     *
     * @covers \ezp\Content\Proxy::__construct
     */
    public function testProxyConstruct()
    {
        $this->assertInstanceOf( "ezp\\Content", new ProxyContent( 1, $this->service ) );
    }

    /**
     * Tests retrieving definition on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::definition
     */
    public function testProxyDefinition()
    {
        $content = new ProxyContent( 1, $this->service );
        $definition = $content->definition();
        $this->assertInternalType( "array", $definition );
        $this->assertEquals( "content", $definition["module"] );
    }

    /**
     * Tests retrieving main location on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getMainLocation
     */
    public function testProxyGetMainLocation()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Location", $content->getMainLocation() );
    }

    /**
     * Tests retrieving versions on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getVersions
     */
    public function testProxyGetVersions()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Version\\LazyCollection", $content->getVersions() );
    }

    /**
     * Tests retrieving current version on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getCurrentVersion
     */
    public function testProxyGetCurrentVersion()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Version", $content->getCurrentVersion() );
    }

    /**
     * Tests retrieving content type on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getContentType
     */
    public function testProxyGetContentType()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Type", $content->getContentType() );
    }

    /**
     * Tests retrieving fields on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getFields
     */
    public function testProxyGetFields()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Field\\LazyCollection", $content->getFields() );
    }

    /**
     * Tests retrieving section on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getSection
     */
    public function testProxyGetSection()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Content\\Section", $content->getSection() );
    }

    /**
     * Tests retrieving owner on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getOwner
     */
    public function testProxyGetOwner()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\User", $content->getOwner() );
    }

    /**
     * Tests retrieving locations on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getLocations
     */
    public function testProxyGetLocations()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Type", $content->getLocations() );
    }

    /**
     * Tests retrieving relations on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getRelations
     */
    public function testProxyGetRelations()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Type", $content->getRelations() );
    }

    /**
     * Tests retrieving reverse relations on a Proxy Content
     *
     * @covers \ezp\Content\Proxy::getReverseRelations
     */
    public function testProxyGetReverseRelations()
    {
        $content = new ProxyContent( 1, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Type", $content->getReverseRelations() );
    }
}
