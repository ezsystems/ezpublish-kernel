<?php
/**
 * File containing the ConfigScopeListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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

    protected function setUp()
    {
        parent::setUp();
        $this->configResolver = $this->getMock( 'eZ\Publish\Core\MVC\Symfony\Configuration\VersatileScopeInterface' );
        $this->viewManager = $this->getMock( 'eZ\Bundle\EzPublishCoreBundle\Tests\EventListener\Stubs\ViewManager' );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
                MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
            ),
            ConfigScopeListener::getSubscribedEvents()
        );
    }

    public function testOnConfigScopeChange()
    {
        $siteAccess = new SiteAccess( 'test' );
        $event = new ScopeChangeEvent( $siteAccess );
        $this->configResolver
            ->expects( $this->once() )
            ->method( 'setDefaultScope' )
            ->with( $siteAccess->name );
        $this->viewManager
            ->expects( $this->once() )
            ->method( 'setSiteAccess' )
            ->with( $siteAccess );

        $listener = new ConfigScopeListener( $this->configResolver, $this->viewManager );
        $listener->onConfigScopeChange( $event );
        $this->assertSame( $siteAccess, $event->getSiteAccess() );
    }
}
