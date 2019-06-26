<?php

/**
 * File containing the SecurityListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Tests\EventListener;

use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener;
use eZ\Publish\Core\MVC\Symfony\Security\InteractiveLoginToken;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface;
use eZ\Publish\Core\MVC\Symfony\Security\Exception\UnauthorizedSiteAccessException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

class SecurityListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $repository;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $configResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $eventDispatcher;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $tokenStorage;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $authChecker;

    /** @var \eZ\Publish\Core\MVC\Symfony\Security\EventListener\SecurityListener */
    protected $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this->createMock(Repository::class);
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->listener = $this->generateListener();
    }

    protected function generateListener()
    {
        return new SecurityListener(
            $this->repository,
            $this->configResolver,
            $this->eventDispatcher,
            $this->tokenStorage,
            $this->authChecker
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                SecurityEvents::INTERACTIVE_LOGIN => [
                    ['onInteractiveLogin', 10],
                    ['checkSiteAccessPermission', 9],
                ],
                KernelEvents::REQUEST => ['onKernelRequest', 7],
            ],
            SecurityListener::getSubscribedEvents()
        );
    }

    public function testOnInteractiveLoginAlreadyEzUser()
    {
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $event = new BaseInteractiveLoginEvent(new Request(), $token);

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->listener->onInteractiveLogin($event);
    }

    public function testOnInteractiveLoginNotUserObject()
    {
        $user = 'foobar';
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $event = new BaseInteractiveLoginEvent(new Request(), $token);

        $this->eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $this->listener->onInteractiveLogin($event);
    }

    public function testOnInteractiveLogin()
    {
        $user = $this->createMock(SymfonyUserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));
        $token
            ->expects($this->once())
            ->method('getRoles')
            ->will($this->returnValue(['ROLE_USER']));
        $token
            ->expects($this->once())
            ->method('getAttributes')
            ->will($this->returnValue(['foo' => 'bar']));

        $event = new BaseInteractiveLoginEvent(new Request(), $token);

        $anonymousUserId = 10;
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('anonymous_user_id')
            ->will($this->returnValue($anonymousUserId));

        $apiUser = $this->createMock(APIUser::class);
        $userService = $this->createMock(UserService::class);
        $userService
            ->expects($this->once())
            ->method('loadUser')
            ->with($anonymousUserId)
            ->will($this->returnValue($apiUser));

        $this->repository
            ->expects($this->once())
            ->method('getUserService')
            ->will($this->returnValue($userService));
        $this->repository
            ->expects($this->once())
            ->method('setCurrentUser')
            ->with($apiUser);

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($this->isInstanceOf(InteractiveLoginToken::class));

        $this->listener->onInteractiveLogin($event);
    }

    /**
     * @expectedException \eZ\Publish\Core\MVC\Symfony\Security\Exception\UnauthorizedSiteAccessException
     */
    public function testCheckSiteAccessPermissionDenied()
    {
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $request = new Request();
        $siteAccess = new SiteAccess();
        $request->attributes->set('siteaccess', $siteAccess);

        $this->authChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(new Attribute('user', 'login', ['valueObject' => $siteAccess])))
            ->will($this->returnValue(false));

        $this->listener->checkSiteAccessPermission(new BaseInteractiveLoginEvent($request, $token));
    }

    public function testCheckSiteAccessPermissionGranted()
    {
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $request = new Request();
        $siteAccess = new SiteAccess();
        $request->attributes->set('siteaccess', $siteAccess);

        $this->authChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with($this->equalTo(new Attribute('user', 'login', ['valueObject' => $siteAccess])))
            ->will($this->returnValue(true));

        // Nothing should happen or should be returned.
        $this->listener->checkSiteAccessPermission(new BaseInteractiveLoginEvent($request, $token));
    }

    public function testCheckSiteAccessNotEzUser()
    {
        $user = $this->createMock(SymfonyUserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $request = new Request();
        $siteAccess = new SiteAccess();
        $request->attributes->set('siteaccess', $siteAccess);

        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->checkSiteAccessPermission(new BaseInteractiveLoginEvent($request, $token));
    }

    public function testCheckSiteAccessNoSiteAccess()
    {
        $user = $this->createMock(UserInterface::class);
        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->checkSiteAccessPermission(new BaseInteractiveLoginEvent(new Request(), $token));
    }

    public function testOnKernelRequestSubRequest()
    {
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::SUB_REQUEST
        );

        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken');
        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestSubRequestFragment()
    {
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('/_fragment'),
            HttpKernelInterface::MASTER_REQUEST
        );
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');

        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken');
        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNoSiteAccess()
    {
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->tokenStorage
            ->expects($this->never())
            ->method('getToken');
        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestNullToken()
    {
        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess());
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null));
        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestLoginRoute()
    {
        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess());
        $request->attributes->set('_route', 'login');
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue(null));
        $this->authChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestAccessDenied()
    {
        $this->expectException(UnauthorizedSiteAccessException::class);

        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess());
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('foo'));

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));
        $this->authChecker
            ->expects($this->once())
            ->method('isGranted')
            ->will($this->returnValue(false));

        $this->listener->onKernelRequest($event);
    }

    public function testOnKernelRequestAccessGranted()
    {
        $request = new Request();
        $request->attributes->set('siteaccess', new SiteAccess());
        $event = new GetResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        $token = $this->createMock(TokenInterface::class);
        $token
            ->expects($this->any())
            ->method('getUsername')
            ->will($this->returnValue('foo'));

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));
        $this->authChecker
            ->expects($this->once())
            ->method('isGranted')
            ->will($this->returnValue(true));

        // Nothing should happen or should be returned.
        $this->listener->onKernelRequest($event);
    }
}
