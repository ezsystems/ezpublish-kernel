<?php
/**
 * File containing the RequestListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
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
        $configResolver = $this->getMock( 'eZ\Publish\Core\MVC\ConfigResolverInterface' );
        $configResolver
            ->expects( $this->once() )
            ->method( 'getParameter' )
            ->with( 'legacy_mode' )
            ->will( $this->returnValue( true ) );
        $userService = $this->getMock( 'eZ\Publish\API\Repository\UserService' );
        $repository = $this->getMock( 'eZ\Publish\API\Repository\Repository' );
        $this->container
            ->expects( $this->any() )
            ->method( 'get' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish.config.resolver', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $configResolver ),
                        array( 'ezpublish.api.repository', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $repository ),
                    )
                )
            );
        $repository
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
        $repository
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
        $listener = new RequestListener( $this->container, $this->securityContext );
        $listener->onKernelRequest( $event );
    }
}
