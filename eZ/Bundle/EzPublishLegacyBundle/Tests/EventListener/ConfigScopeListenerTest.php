<?php
/**
 * File containing the ConfigScopeListenerTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Tests\EventListener;

use eZ\Bundle\EzPublishLegacyBundle\EventListener\ConfigScopeListener;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConfigScopeListenerTest extends PHPUnit_Framework_TestCase
{
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
        $container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
        $container
            ->expects( $this->exactly( 3 ) )
            ->method( 'set' )
            ->will(
                $this->returnValueMap(
                    array(
                        array( 'ezpublish_legacy.kernel', null, ContainerInterface::SCOPE_CONTAINER, null ),
                        array( 'ezpublish_legacy.kernel.lazy', null, ContainerInterface::SCOPE_CONTAINER, null ),
                        array( 'ezpublish_legacy.kernel_handler.web', null, ContainerInterface::SCOPE_CONTAINER, null ),
                    )
                )
            );

        $listener = new ConfigScopeListener( $container );
        $siteAccess = new SiteAccess( 'test' );
        $event = new ScopeChangeEvent( $siteAccess );
        $listener->onConfigScopeChange( $event );
        $this->assertSame( $siteAccess, $event->getSiteAccess() );
    }
}
