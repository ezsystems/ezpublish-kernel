<?php
/**
 * File containing the KernelTestTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use eZ\Bundle\EzPublishCoreBundle\Kernel;

class KernelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::generateUserHash
     */
    public function testGenerateUserHashAnonymous()
    {
        $request = new Request();
        $request->headers->add(
            array(
                // X-User-Hash should be removed
                'X-User-Hash' => 'fooHash',
                'X-Something-else' => 'whatever'
            )
        );

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assertSame( Kernel::ANONYMOUS_HASH, $kernel->generateUserHash( $request ) );
        $this->assertFalse( $request->headers->has( 'X-User-Hash' ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::generateUserHash
     */
    public function testGenerateUserHashNoCache()
    {
        $request = new Request();
        $request->headers->add(
            array(
                // X-User-Hash should be removed
                'X-User-Hash' => 'fooHash',
                'X-Something-else' => 'whatever'
            )
        );
        $request->cookies->set( 'is_logged_in', 'true' );
        $hash = '123abc';

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'handle', 'getCachePool' ) )
            ->getMockForAbstractClass();

        // Set expectations for cache.
        // Note that we will call generateUserHash() twice.
        // The first time, cache is being generated, the second time hash is stored in memory.
        $cacheItem = $this
            ->getMockBuilder( 'Stash\\Item' )
            ->disableOriginalConstructor()
            ->getMock();
        $cacheItem
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will(
                $this->returnValue( true )
            );
        $cacheItem
            ->expects( $this->once() )
            ->method( 'set' )
            ->with( $hash, 600 );
        $cachePool = $this->getMock( 'Stash\\Pool' );
        $cachePool
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->will( $this->returnValue( $cacheItem ) );
        $kernel
            ->expects( $this->once() )
            ->method( 'getCachePool' )
            ->will( $this->returnValue( $cachePool ) );

        $response = new Response( '', 200, array( 'X-User-Hash' => $hash ) );
        $kernel
            ->expects( $this->once() )
            ->method( 'handle' )
            ->will( $this->returnValue( $response ) );

        $this->assertSame( $hash, $kernel->generateUserHash( $request ) );
        $this->assertFalse( $request->headers->has( 'X-User-Hash' ) );
        $this->assertSame( $hash, $kernel->generateUserHash( $request ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::generateUserHash
     */
    public function testGenerateUserHashCacheFresh()
    {
        $request = new Request();
        $request->headers->add(
            array(
                // X-User-Hash should be removed
                'X-User-Hash' => 'fooHash',
                'X-Something-else' => 'whatever'
            )
        );
        $request->cookies->set( 'is_logged_in', 'true' );
        $hash = '123abc';

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'handle', 'getCachePool' ) )
            ->getMockForAbstractClass();

        // Set expectactions for cache.
        $cacheItem = $this
            ->getMockBuilder( 'Stash\\Item' )
            ->disableOriginalConstructor()
            ->getMock();
        $cacheItem
            ->expects( $this->once() )
            ->method( 'isMiss' )
            ->will(
                $this->returnValue( false )
            );
        $cacheItem
            ->expects( $this->never() )
            ->method( 'set' );
        $cacheItem
            ->expects( $this->once() )
            ->method( 'get' )
            ->will( $this->returnValue( $hash ) );
        $cachePool = $this->getMock( 'Stash\\Pool' );
        $cachePool
            ->expects( $this->once() )
            ->method( 'getItem' )
            ->will( $this->returnValue( $cacheItem ) );
        $kernel
            ->expects( $this->once() )
            ->method( 'getCachePool' )
            ->will( $this->returnValue( $cachePool ) );

        $kernel
            ->expects( $this->never() )
            ->method( 'handle' );

        $this->assertSame( $hash, $kernel->generateUserHash( $request ) );
        $this->assertFalse( $request->headers->has( 'X-User-Hash' ) );
        $this->assertSame( $hash, $kernel->generateUserHash( $request ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::getCachePool
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::getCacheDriver
     */
    public function testGetCachePool()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'getCacheDriver' ) )
            ->getMockForAbstractClass();

        $kernel
            ->expects( $this->once() )
            ->method( 'getCacheDriver' )
            ->will( $this->returnValue( $this->getMock( 'Stash\\Driver\\DriverInterface' ) ) );

        $cachePool = $kernel->getCachePool();
        $this->assertInstanceOf( 'Stash\\Pool', $cachePool );
        $this->assertSame( $cachePool, $kernel->getCachePool() );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::handle
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::isUserHashRequest
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::canGenerateUserHash
     */
    public function testHandleAuthenticateNotAllowed()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $request = new Request();
        $request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $request->server->set( 'REMOTE_ADDR', '10.11.12.13' );
        $response = $kernel->handle( $request );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertSame( 405, $response->getStatusCode() );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::handle
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::isUserHashRequest
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::canGenerateUserHash
     */
    public function testHandleAuthenticate()
    {
        $hash = '123abc';
        $request = new Request();
        $request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $request->server->set( 'REMOTE_ADDR', '127.0.0.1' );

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'generateUserHash' ) )
            ->getMockForAbstractClass();
        $kernel
            ->expects( $this->once() )
            ->method( 'generateUserHash' )
            ->with( $request )
            ->will( $this->returnValue( $hash ) );

        $response = $kernel->handle( $request );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertSame( 200, $response->getStatusCode() );
        $this->assertSame( $hash, $response->headers->get( 'X-User-Hash' ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::handle
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::isUserHashRequest
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::canGenerateUserHash
     */
    public function testHandleAuthenticateWithTrustedProxy()
    {
        Request::setTrustedProxies( array( '10.11.12.13' ) );

        $hash = '123abc';
        $request = new Request();
        $request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $request->server->set( 'REMOTE_ADDR', '10.11.12.13' );

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'generateUserHash' ) )
            ->getMockForAbstractClass();
        $kernel
            ->expects( $this->once() )
            ->method( 'generateUserHash' )
            ->with( $request )
            ->will( $this->returnValue( $hash ) );

        $response = $kernel->handle( $request );
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertSame( 200, $response->getStatusCode() );
        $this->assertSame( $hash, $response->headers->get( 'X-User-Hash' ) );
    }

    /**
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::handle
     * @covers eZ\Bundle\EzPublishCoreBundle\Kernel::isUserHashRequest
     */
    public function testHandleRegular()
    {
        $request = new Request();

        /** @var \PHPUnit_Framework_MockObject_MockObject|Kernel $kernel */
        $kernel = $this
            ->getMockBuilder( 'eZ\\Bundle\\EzPublishCoreBundle\\Kernel' )
            ->disableOriginalConstructor()
            ->setMethods( array( 'boot', 'getHttpKernel' ) )
            ->getMockForAbstractClass();
        $httpKernel = $this->getMock( 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' );
        $response = new Response();
        $httpKernel
            ->expects( $this->once() )
            ->method( 'handle' )
            ->with( $request )
            ->will( $this->returnValue( $response ) );
        $kernel
            ->expects( $this->once() )
            ->method( 'getHttpKernel' )
            ->will( $this->returnValue( $httpKernel ) );

        $this->assertSame( $response, $kernel->handle( $request ) );
    }
}
