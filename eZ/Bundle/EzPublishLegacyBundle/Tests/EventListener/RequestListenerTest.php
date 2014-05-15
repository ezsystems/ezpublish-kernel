<?php
/**
 * File containing the RequestListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use PHPUnit_Framework_TestCase;
use eZ\Bundle\EzPublishLegacyBundle\EventListener\RequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $this->repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->securityContext = $this->getMock( 'Symfony\Component\Security\Core\SecurityContextInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                KernelEvents::REQUEST => 'onKernelRequest'
            ),
            Requestlistener::getSubscribedEvents()
        );
    }

    public function testOnKernelRequest()
    {
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( true ) );
        $userService = $this->getMock( 'eZ\Publish\API\Repository\UserService' );
        $this->repository
            ->expects( $this->once() )
            ->method( 'getUserService' )
            ->will( $this->returnValue( $userService ) );

        $userId = 123;
        $apiUser = $this->getMock( 'eZ\Publish\API\Repository\Values\User\User' );
        $userService
            ->expects( $this->once() )
            ->method( 'loadUser' )
            ->with( $userId )
            ->will( $this->returnValue( $apiUser ) );
        $this->repository
            ->expects( $this->once() )
            ->method( 'setCurrentUser' )
            ->with( $apiUser );

        $session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        $request = $this->getMock( 'Symfony\Component\HttpFoundation\Request', array( 'getSession' ) );
        $request
            ->expects( $this->any() )
            ->method( 'getSession' )
            ->will( $this->returnValue( $session ) );
        $session
            ->expects( $this->once() )
            ->method( 'has' )
            ->with( 'eZUserLoggedInID' )
            ->will( $this->returnValue( true ) );
        $session
            ->expects( $this->once() )
            ->method( 'get' )
            ->with( 'eZUserLoggedInID' )
            ->will( $this->returnValue( $userId ) );

        $token = $this->getMock( 'Symfony\Component\Security\Core\Authentication\Token\TokenInterface' );
        $this->securityContext
            ->expects( $this->once() )
            ->method( 'getToken' )
            ->will(
                $this->returnValue( $token )
            );
        $token
            ->expects( $this->once() )
            ->method( 'setUser' )
            ->with( $this->isInstanceOf( 'eZ\Publish\Core\MVC\Symfony\Security\User' ) );
        $token
            ->expects( $this->once() )
            ->method( 'setAuthenticated' )
            ->with( true );

        $event = new GetResponseEvent(
            $this->getMock( 'Symfony\Component\HttpKernel\HttpKernelInterface' ),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );
        $listener = new RequestListener( $this->configResolver, $this->repository, $this->securityContext );
        $listener->onKernelRequest( $event );
    }
}
