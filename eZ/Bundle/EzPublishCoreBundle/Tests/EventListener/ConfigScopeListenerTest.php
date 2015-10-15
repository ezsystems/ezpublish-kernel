<?php

/**
 * File containing the ConfigScopeListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\ConfigScopeListener;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;

class ConfigScopeListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $viewProviders;

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock('eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface');
        $this->viewManager = $this->getMock('eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\ViewManager');
        $this->viewProviders = array(
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\ViewProvider'),
            $this->getMock('eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\ViewProvider'),
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::CONFIG_SCOPE_CHANGE => array('onConfigScopeChange', 100),
                MVCEvents::CONFIG_SCOPE_RESTORE => array('onConfigScopeChange', 100),
            ),
            ConfigScopeListener::getSubscribedEvents()
        );
    }

    public function testOnConfigScopeChange()
    {
        $siteAccess = new SiteAccess('test');
        $event = new ScopeChangeEvent($siteAccess);
        $this->configResolver
            ->expects($this->once())
            ->method('setDefaultScope')
            ->with($siteAccess->name);
        $this->viewManager
            ->expects($this->once())
            ->method('setSiteAccess')
            ->with($siteAccess);

        foreach ($this->viewProviders as $viewProvider) {
            $viewProvider
                ->expects($this->once())
                ->method('setSiteAccess')
                ->with($siteAccess);
        }

        $listener = new ConfigScopeListener($this->configResolver, $this->viewManager);
        $listener->setViewProviders($this->viewProviders);
        $listener->onConfigScopeChange($event);
        $this->assertSame($siteAccess, $event->getSiteAccess());
    }
}
