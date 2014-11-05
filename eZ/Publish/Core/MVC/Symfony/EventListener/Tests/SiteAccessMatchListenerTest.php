<?php
/**
 * File containing the SiteAccessMatchListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\EventListener\Tests;

use eZ\Publish\Core\MVC\Symfony\EventListener\SiteAccessMatchListener;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class SiteAccessMatchListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $saRouter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userHashMatcher;

    /**
     * @var SiteAccessMatchListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->saRouter = $this->getMockBuilder( '\eZ\Publish\Core\MVC\Symfony\SiteAccess\Router' )
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMock( '\Symfony\Component\EventDispatcher\EventDispatcherInterface' );
        $this->userHashMatcher = $this->getMock( '\Symfony\Component\HttpFoundation\RequestMatcherInterface' );
        $this->listener = new SiteAccessMatchListener( $this->saRouter, $this->eventDispatcher, $this->userHashMatcher );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array( KernelEvents::REQUEST => array( 'onKernelRequest', 45 ) ),
            SiteAccessMatchListener::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestUserHash()
    {
        $request = new Request();
        $event = new GetResponseEvent(
            $this->getMock( '\Symfony\Component\HttpKernel\HttpKernelInterface' ),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->userHashMatcher
            ->expects( $this->once() )
            ->method( 'matches' )
            ->with( $request )
            ->will( $this->returnValue( true ) );

        $this->saRouter
            ->expects( $this->never() )
            ->method( 'match' );
        $this->eventDispatcher
            ->expects( $this->never() )
            ->method( 'dispatch' );

        $this->listener->onKernelRequest( $event );
        $this->assertFalse( $request->attributes->has( 'siteaccess' ) );
    }
}
