<?php
/**
 * File containing the RequestEventListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\RequestEventListener;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use eZ\Bundle\EzPublishCoreBundle\Kernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

class RequestEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var RequestEventListener
     */
    private $requestEventListener;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var GetResponseEvent
     */
    private $event;

    /**
     * @var HttpKernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $httpKernel;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->getMock( 'Symfony\\Component\\DependencyInjection\\ContainerInterface' );
        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );

        $this->requestEventListener = new RequestEventListener( $this->container, $this->logger );

        $this->request = $this
            ->getMockBuilder( 'Symfony\\Component\\HttpFoundation\\Request' )
            ->setMethods( array( 'getSession', 'hasSession' ) )
            ->getMock();

        $this->httpKernel = $this->getMock( 'Symfony\\Component\\HttpKernel\\HttpKernelInterface' );
        $this->event = new GetResponseEvent(
            $this->httpKernel,
            $this->request,
            HttpKernelInterface::MASTER_REQUEST
        );
    }

    public function testSubscribedEvents()
    {
        $this->assertSame(
            array(
                KernelEvents::REQUEST => array(
                    array( 'onKernelRequestSetup', 190 ),
                    array( 'onKernelRequestForward', 10 ),
                    array( 'onKernelRequestRedirect', 0 ),
                    array( 'onKernelRequestUserHash', 7 ),
                    array( 'onKernelRequestIndex', 40 ),
                )
            ),
            $this->requestEventListener->getSubscribedEvents()
        );
    }

    /**
     * @dataProvider indexPageProvider
     */
    public function testOnKernelRequestIndexOnIndexPage( $requestPath, $configuredIndexPath, $expectedIndexPath )
    {
        $mockResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->container
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'ezpublish.config.resolver' )
            ->will( $this->returnValue( $mockResolver ) );
        $mockResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'index_page' )
            ->will( $this->returnValue( $configuredIndexPath ) );
        $this->request->attributes->set( 'semanticPathinfo', $requestPath );
        $this->requestEventListener->onKernelRequestIndex( $this->event );
        $this->assertEquals( $expectedIndexPath, $this->request->attributes->get( 'semanticPathinfo' ) );
        $this->assertTrue( $this->request->attributes->get( 'needsForward' ) );
    }

    public function indexPageProvider()
    {
        return array(
            array( '/', '/foo', '/foo' ),
            array( '/', '/foo/', '/foo/' ),
            array( '/', '/foo/bar', '/foo/bar' ),
            array( '/', 'foo/bar', '/foo/bar' ),
            array( '', 'foo/bar', '/foo/bar' ),
            array( '', '/foo/bar', '/foo/bar' ),
            array( '', '/foo/bar/', '/foo/bar/' ),
        );
    }

    public function testOnKernelRequestIndexNotOnIndexPage()
    {
        $this->request->attributes->set( 'semanticPathinfo', '/anyContent' );
        $this->requestEventListener->onKernelRequestIndex( $this->event );
        $this->assertFalse( $this->request->attributes->has( 'needsForward' ) );
    }

    public function testOnKernelRequestUserHashNotAuthenticate()
    {
        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertFalse( $this->event->hasResponse() );
        $this->assertFalse( $this->event->isPropagationStopped() );
    }

    public function testOnKernelRequestUserHashAuthenticateNoSession()
    {
        $this->request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );

        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertTrue( $this->event->hasResponse() );
        $this->assertTrue( $this->event->isPropagationStopped() );
        $this->assertSame( 400, $this->event->getResponse()->getStatusCode() );
    }

    public function testOnKernelRequestUserHash()
    {
        $hashGenerator = $this->getMock( 'eZ\\Publish\\SPI\\HashGenerator' );
        $hash = '123abc';
        $hashGenerator
            ->expects( $this->once() )
            ->method( 'generate' )
            ->will( $this->returnValue( $hash ) );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.user.hash_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $hashGenerator ),
                        array( 'logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger ),
                    )
                )
            );

        $this->request->headers->add(
            array(
                'X-HTTP-Override' => 'AUTHENTICATE',
                'Accept' => Kernel::USER_HASH_ACCEPT_HEADER
            )
        );
        $this->request
            ->expects( $this->once() )
            ->method( 'hasSession' )
            ->will( $this->returnValue( true ) );

        $this->assertNull( $this->requestEventListener->onKernelRequestUserHash( $this->event ) );
        $this->assertTrue( $this->event->isPropagationStopped() );
        $this->assertTrue( $this->event->hasResponse() );
        $response = $this->event->getResponse();
        $this->assertInstanceOf( 'Symfony\\Component\\HttpFoundation\\Response', $response );
        $this->assertTrue( $response->headers->has( 'X-User-Hash' ) );
        $this->assertSame( $hash, $response->headers->get( 'X-User-Hash' ) );
    }

    public function testOnKernelRequestForwardSubRequest()
    {
        $this->httpKernel
            ->expects( $this->never() )
            ->method( 'handle' );

        $event = new GetResponseEvent( $this->httpKernel, new Request, HttpKernelInterface::SUB_REQUEST );
        $this->requestEventListener->onKernelRequestForward( $event );
    }

    public function testOnKernelRequestForward()
    {
        $queryParameters = array( 'some' => 'thing' );
        $cookieParameters = array( 'cookie' => 'value' );
        $request = Request::create( '/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters );
        $semanticPathinfo = '/foo/something';
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( 'needsForward', true );
        $request->attributes->set( 'someAttribute', 'someValue' );

        $expectedForwardRequest = Request::create( $semanticPathinfo, 'GET', $queryParameters, $cookieParameters );
        $expectedForwardRequest->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $expectedForwardRequest->attributes->set( 'someAttribute', 'someValue' );

        $response = new Response( 'Success!' );
        $this->httpKernel
            ->expects( $this->once() )
            ->method( 'handle' )
            ->with( $this->equalTo( $expectedForwardRequest ) )
            ->will( $this->returnValue( $response ) );

        $event = new GetResponseEvent( $this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST );
        $this->requestEventListener->onKernelRequestForward( $event );
        $this->assertSame( $response, $event->getResponse() );
        $this->assertTrue( $event->isPropagationStopped() );
    }

    public function testOnKernelRequestSetupSubrequest()
    {
        $this->container
            ->expects( $this->never() )
            ->method( 'hasParameter' );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );

        $event = new GetResponseEvent( $this->httpKernel, new Request, HttpKernelInterface::SUB_REQUEST );
        $this->requestEventListener->onKernelRequestSetup( $event );
        $this->assertFalse( $event->hasResponse() );
    }

    public function testOnKernelRequestSetupAlreadyHasSiteaccess()
    {
        $this->container
            ->expects( $this->once() )
            ->method( 'hasParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( 'foo' ) );
        $event = new GetResponseEvent( $this->httpKernel, new Request, HttpKernelInterface::MASTER_REQUEST );
        $this->requestEventListener->onKernelRequestSetup( $event );
        $this->assertFalse( $event->hasResponse() );
    }

    public function testOnKernelRequestSetupAlreadySetupUri()
    {
        $router = $this->getMock( 'Symfony\Component\Routing\RouterInterface' );
        $this->container
            ->expects( $this->once() )
            ->method( 'hasParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( 'setup' ) );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $router ),
                        array( 'router.request_context', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, new RequestContext ),
                    )
                )
            );

        $router
            ->expects( $this->once() )
            ->method( 'generate' )
            ->with( 'ezpublishSetup' )
            ->will( $this->returnValue( '/setup' ) );

        $requestEventListener = new RequestEventListener( $this->container, $this->logger );
        $event = new GetResponseEvent( $this->httpKernel, Request::create( '/setup' ), HttpKernelInterface::MASTER_REQUEST );
        $requestEventListener->onKernelRequestSetup( $event );
        $this->assertFalse( $event->hasResponse() );
    }

    public function testOnKernelRequestSetup()
    {
        $router = $this->getMock( 'Symfony\Component\Routing\RouterInterface' );
        $this->container
            ->expects( $this->once() )
            ->method( 'hasParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( true ) );
        $this->container
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'ezpublish.siteaccess.default' )
            ->will( $this->returnValue( 'setup' ) );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'router', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $router ),
                        array( 'router.request_context', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, new RequestContext ),
                    )
                )
            );

        $router
            ->expects( $this->once() )
            ->method( 'generate' )
            ->with( 'ezpublishSetup' )
            ->will( $this->returnValue( '/setup' ) );

        $requestEventListener = new RequestEventListener( $this->container, $this->logger );
        $event = new GetResponseEvent( $this->httpKernel, Request::create( '/foo/bar' ), HttpKernelInterface::MASTER_REQUEST );
        $requestEventListener->onKernelRequestSetup( $event );
        $this->assertTrue( $event->hasResponse() );
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\RedirectResponse', $response );
        $this->assertSame( '/setup', $response->getTargetUrl() );
    }

    public function testOnKernelRequestRedirectSubRequest()
    {
        $event = new GetResponseEvent( $this->httpKernel, new Request, HttpKernelInterface::SUB_REQUEST );
        $this->requestEventListener->onKernelRequestRedirect( $event );
        $this->assertFalse( $event->hasResponse() );
    }

    public function testOnKernelRequestRedirect()
    {
        $queryParameters = array( 'some' => 'thing' );
        $cookieParameters = array( 'cookie' => 'value' );
        $request = Request::create( '/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters );
        $semanticPathinfo = '/foo/something';
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( 'needsRedirect', true );
        $request->attributes->set( 'siteaccess', new SiteAccess() );

        $event = new GetResponseEvent( $this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST );
        $this->requestEventListener->onKernelRequestRedirect( $event );
        $this->assertTrue( $event->hasResponse() );
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\RedirectResponse', $response );
        $this->assertSame( "$semanticPathinfo?some=thing", $response->getTargetUrl() );
        $this->assertSame( 301, $response->getStatusCode() );
        $this->assertTrue( $event->isPropagationStopped() );
    }

    public function testOnKernelRequestRedirectPrependSiteaccess()
    {
        $queryParameters = array( 'some' => 'thing' );
        $cookieParameters = array( 'cookie' => 'value' );
        $siteaccessMatcher = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\SiteAccess\URILexer' );
        $siteaccess = new SiteAccess( 'test', 'foo', $siteaccessMatcher );
        $semanticPathinfo = '/foo/something';

        $request = Request::create( '/test_sa/foo/bar', 'GET', $queryParameters, $cookieParameters );
        $request->attributes->set( 'semanticPathinfo', $semanticPathinfo );
        $request->attributes->set( 'needsRedirect', true );
        $request->attributes->set( 'siteaccess', $siteaccess );
        $request->attributes->set( 'prependSiteaccessOnRedirect', true );

        $expectedURI = "/test$semanticPathinfo";
        $siteaccessMatcher
            ->expects( $this->once() )
            ->method( 'analyseLink' )
            ->with( $semanticPathinfo )
            ->will( $this->returnValue( $expectedURI ) );

        $event = new GetResponseEvent( $this->httpKernel, $request, HttpKernelInterface::MASTER_REQUEST );
        $this->requestEventListener->onKernelRequestRedirect( $event );
        $this->assertTrue( $event->hasResponse() );
        /** @var RedirectResponse $response */
        $response = $event->getResponse();
        $this->assertInstanceOf( 'Symfony\Component\HttpFoundation\RedirectResponse', $response );
        $this->assertSame( "$expectedURI?some=thing", $response->getTargetUrl() );
        $this->assertSame( 301, $response->getStatusCode() );
        $this->assertTrue( $event->isPropagationStopped() );
    }
}
