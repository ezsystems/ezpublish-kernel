<?php

/**
 * File containing the SessionSetDynamicNameListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\SessionSetDynamicNameListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SessionSetDynamicNameListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionStorage;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
        $this->session = $this->getMock('Symfony\Component\HttpFoundation\Session\SessionInterface');
        $this->sessionStorage = $this->getMock('Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage');
    }

    public function testGetSubscribedEvents()
    {
        $listener = new SessionSetDynamicNameListener($this->configResolver, $this->session, $this->sessionStorage);
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array('onSiteAccessMatch', 250),
            ),
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
            $this->getMock('Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface')
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
        return array(
            array(new SiteAccess('foo'), array('name' => 'eZSESSID'), array('name' => 'eZSESSID')),
            array(new SiteAccess('foo'), array('name' => 'eZSESSID{siteaccess_hash}'), array('name' => 'eZSESSID' . md5('foo'))),
            array(new SiteAccess('foo'), array('name' => 'this_is_a_session_name'), array('name' => 'eZSESSID_this_is_a_session_name')),
            array(new SiteAccess('foo'), array('name' => 'something{siteaccess_hash}'), array('name' => 'eZSESSID_something' . md5('foo'))),
            array(new SiteAccess('bar_baz'), array('name' => '{siteaccess_hash}something'), array('name' => 'eZSESSID_' . md5('bar_baz') . 'something')),
            array(
                new SiteAccess('foo'),
                array(
                    'name' => 'this_is_a_session_name',
                    'cookie_path' => '/foo',
                    'cookie_domain' => 'foo.com',
                    'cookie_lifetime' => 86400,
                    'cookie_secure' => false,
                    'cookie_httponly' => true,
                ),
                array(
                    'name' => 'eZSESSID_this_is_a_session_name',
                    'cookie_path' => '/foo',
                    'cookie_domain' => 'foo.com',
                    'cookie_lifetime' => 86400,
                    'cookie_secure' => false,
                    'cookie_httponly' => true,
                ),
            ),
        );
    }

    public function testOnSiteAccessMatchNoConfiguredSessionName()
    {
        $configuredSessionStorageOptions = array('cookie_path' => '/bar');
        $sessionName = 'some_default_name';
        $sessionOptions = $configuredSessionStorageOptions + array('name' => "eZSESSID_$sessionName");

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
