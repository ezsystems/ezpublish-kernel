<?php
/**
 * File contains: ezp\User\Tests\ProxyTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Tests;
use ezp\User\Proxy as ProxyUser,
    ezp\Base\ServiceContainer,
    ezp\Base\Configuration,
    PHPUnit_Framework_TestCase;

/**
 * Test case for Content class
 *
 */
class ProxyTest extends PHPUnit_Framework_TestCase
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
     * @var \ezp\User\Service
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
        $this->service = $this->repository->getUserService();
    }

    /**
     * Tests User\Proxy creation
     *
     * @covers \ezp\User\Proxy::__construct
     */
    public function testProxyConstruct()
    {
        $this->assertInstanceOf( "ezp\\User", new ProxyUser( 1, $this->service ) );
    }

    /**
     * Tests retrieving definition on a Proxy User
     *
     * @covers \ezp\User\Proxy::definition
     */
    public function testProxyDefinition()
    {
        $user = new ProxyUser( 10, $this->service );
        $definition = $user->definition();
        $this->assertInternalType( "array", $definition );
        $this->assertEquals( "user", $definition["module"] );
    }

    /**
     * Tests retrieving groups on a Proxy User
     *
     * @covers \ezp\User\Proxy::getGroups
     */
    public function testProxyGetGroups()
    {
        $user = new ProxyUser( 10, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Lazy", $user->getGroups() );
    }

    /**
     * Tests retrieving roles on a Proxy User
     *
     * @covers \ezp\User\Proxy::getRoles
     */
    public function testProxyGetRoles()
    {
        $user = new ProxyUser( 10, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Lazy", $user->getRoles() );
    }

    /**
     * Tests retrieving policies on a Proxy User
     *
     * @covers \ezp\User\Proxy::getPolicies
     */
    public function testProxyGetPolicies()
    {
        $user = new ProxyUser( 10, $this->service );
        $this->assertInstanceOf( "ezp\\Base\\Collection\\Lazy", $user->getPolicies() );
    }

    /**
     * Tests retrieving policies on a Proxy User
     *
     * @covers \ezp\User\Proxy::hasAccessTo
     */
    public function testProxyHasAccessTo()
    {
        $user = new ProxyUser( 10, $this->service );
        $this->assertSame( false, $user->hasAccessTo( "content", "create" ) );
    }
}
