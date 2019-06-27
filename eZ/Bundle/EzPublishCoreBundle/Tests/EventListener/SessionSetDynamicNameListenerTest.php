<?php

/**
 * File containing the SessionSetDynamicNameListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\SessionSetDynamicNameListener;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface as SymfonySessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionSetDynamicNameListenerTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $sessionStorage;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMockBuilder(ConfigResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(SymfonySessionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionStorage = $this->getMockBuilder(NativeSessionStorage::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSubscribedEvents()
    {
        $listener = new SessionSetDynamicNameListener($this->configResolver, $this->session, $this->sessionStorage);
        $this->assertSame(
            [
                MVCEvents::SITEACCESS => ['onSiteAccessMatch', 250],
            ],
            $listener->getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchNoSession()
    {
        $this->sessionStorage
            ->expects($this->never())
            ->method('setOptions');
        $listener = new SessionSetDynamicNameListener($this->configResolver, null, $this->sessionStorage);
        $listener->onSiteAccessMatch(new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST));
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $this->sessionStorage
            ->expects($this->never())
            ->method('setOptions');
        $listener = new SessionSetDynamicNameListener($this->configResolver, $this->session, $this->sessionStorage);
        $listener->onSiteAccessMatch(new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST));
    }

    public function testOnSiteAccessMatchNonNativeSessionStorage()
    {
        $this->configResolver
            ->expects($this->never())
            ->method('getParameter');
        $listener = new SessionSetDynamicNameListener(
            $this->configResolver,
            $this->session,
            $this->createMock(SessionStorageInterface::class)
        );
        $listener->onSiteAccessMatch(new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::SUB_REQUEST));
    }

    /**
     * @dataProvider onSiteAccessMatchProvider
     */
    public function testOnSiteAccessMatch(SiteAccess $siteAccess, $configuredSessionStorageOptions, array $expectedSessionStorageOptions)
    {
        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $this->sessionStorage
            ->expects($this->once())
            ->method('setOptions')
            ->with($expectedSessionStorageOptions);
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('session')
            ->will($this->returnValue($configuredSessionStorageOptions));

        $listener = new SessionSetDynamicNameListener($this->configResolver, $this->session, $this->sessionStorage);
        $listener->onSiteAccessMatch(new PostSiteAccessMatchEvent($siteAccess, new Request(), HttpKernelInterface::MASTER_REQUEST));
    }

    public function onSiteAccessMatchProvider()
    {
        return [
            [new SiteAccess('foo'), ['name' => 'eZSESSID'], ['name' => 'eZSESSID']],
            [new SiteAccess('foo'), ['name' => 'eZSESSID{siteaccess_hash}'], ['name' => 'eZSESSID' . md5('foo')]],
            [new SiteAccess('foo'), ['name' => 'this_is_a_session_name'], ['name' => 'eZSESSID_this_is_a_session_name']],
            [new SiteAccess('foo'), ['name' => 'something{siteaccess_hash}'], ['name' => 'eZSESSID_something' . md5('foo')]],
            [new SiteAccess('bar_baz'), ['name' => '{siteaccess_hash}something'], ['name' => 'eZSESSID_' . md5('bar_baz') . 'something']],
            [
                new SiteAccess('foo'),
                [
                    'name' => 'this_is_a_session_name',
                    'cookie_path' => '/foo',
                    'cookie_domain' => 'foo.com',
                    'cookie_lifetime' => 86400,
                    'cookie_secure' => false,
                    'cookie_httponly' => true,
                ],
                [
                    'name' => 'eZSESSID_this_is_a_session_name',
                    'cookie_path' => '/foo',
                    'cookie_domain' => 'foo.com',
                    'cookie_lifetime' => 86400,
                    'cookie_secure' => false,
                    'cookie_httponly' => true,
                ],
            ],
        ];
    }

    public function testOnSiteAccessMatchNoConfiguredSessionName()
    {
        $configuredSessionStorageOptions = ['cookie_path' => '/bar'];
        $sessionName = 'some_default_name';
        $sessionOptions = $configuredSessionStorageOptions + ['name' => "eZSESSID_$sessionName"];

        $this->session
            ->expects($this->once())
            ->method('isStarted')
            ->will($this->returnValue(false));
        $this->session
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('some_default_name'));
        $this->sessionStorage
            ->expects($this->once())
            ->method('setOptions')
            ->with($sessionOptions);
        $this->configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('session')
            ->will($this->returnValue($configuredSessionStorageOptions));

        $listener = new SessionSetDynamicNameListener($this->configResolver, $this->session, $this->sessionStorage);
        $listener->onSiteAccessMatch(new PostSiteAccessMatchEvent(new SiteAccess(), new Request(), HttpKernelInterface::MASTER_REQUEST));
    }
}
