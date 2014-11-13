<?php
/**
 * File containing the DynamicSettingsListenerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\EventListener;

use eZ\Bundle\EzPublishCoreBundle\EventListener\DynamicSettingsListener;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DynamicSettingsListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        parent::setUp();
        $this->container = $this->getMock( 'Symfony\Component\DependencyInjection\ContainerInterface' );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            array(
                MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 254 ),
                MVCEvents::CONFIG_SCOPE_CHANGE => array( 'onConfigScopeChange', 90 ),
                MVCEvents::CONFIG_SCOPE_RESTORE => array( 'onConfigScopeChange', 90 )
            ),
            DynamicSettingsListener::getSubscribedEvents()
        );
    }

    public function testOnSiteAccessMatchSubRequest()
    {
        $event = new PostSiteAccessMatchEvent( new SiteAccess( 'test' ), new Request(), HttpKernelInterface::SUB_REQUEST );
        $resettableServices = array( 'foo', 'bar.baz' );
        $dynamicSettingsServiceIds = array( 'something', 'something_else' );

        $this->container
            ->expects( $this->never() )
            ->method( 'set' );
        $this->container
            ->expects( $this->never() )
            ->method( 'get' );

        $listener = new DynamicSettingsListener( $resettableServices, $dynamicSettingsServiceIds );
        $listener->setContainer( $this->container );
        $listener->onSiteAccessMatch( $event );
    }

    public function testOnSiteAccessMatch()
    {
        $event = new PostSiteAccessMatchEvent( new SiteAccess( 'test' ), new Request(), HttpKernelInterface::MASTER_REQUEST );
        $resettableServices = array( 'foo', 'bar.baz' );
        $dynamicSettingsServiceIds = array( 'something', 'something_else' );

        $this->container
            ->expects( $this->at( 0 ) )
            ->method( 'set' )
            ->with( 'foo', null );
        $this->container
            ->expects( $this->at( 1 ) )
            ->method( 'set' )
            ->with( 'bar.baz', null );

        $fakeService1 = new \stdClass;
        $this->container
            ->expects( $this->at( 2 ) )
            ->method( 'set' )
            ->with( 'something', null );
        $this->container
            ->expects( $this->at( 3 ) )
            ->method( 'get' )
            ->with( 'something' )
            ->will( $this->returnValue( $fakeService1 ) );
        $this->container
            ->expects( $this->at( 4 ) )
            ->method( 'set' )
            ->with( 'something', $fakeService1 );

        $fakeService2 = new \stdClass;
        $this->container
            ->expects( $this->at( 5 ) )
            ->method( 'set' )
            ->with( 'something_else', null );
        $this->container
            ->expects( $this->at( 6 ) )
            ->method( 'get' )
            ->with( 'something_else' )
            ->will( $this->returnValue( $fakeService2 ) );
        $this->container
            ->expects( $this->at( 7 ) )
            ->method( 'set' )
            ->with( 'something_else', $fakeService2 );

        $listener = new DynamicSettingsListener( $resettableServices, $dynamicSettingsServiceIds );
        $listener->setContainer( $this->container );
        $listener->onSiteAccessMatch( $event );
    }

    public function testOnConfigScopeChange()
    {
        $siteAccess = new SiteAccess( 'test' );
        $event = new ScopeChangeEvent( $siteAccess );
        $resettableServices = array( 'foo', 'bar.baz' );
        $dynamicSettingsServiceIds = array( 'something', 'something_else' );

        $this->container
            ->expects( $this->at( 0 ) )
            ->method( 'set' )
            ->with( 'foo', null );
        $this->container
            ->expects( $this->at( 1 ) )
            ->method( 'set' )
            ->with( 'bar.baz', null );

        $fakeService1 = new \stdClass;
        $this->container
            ->expects( $this->at( 2 ) )
            ->method( 'set' )
            ->with( 'something', null );
        $this->container
            ->expects( $this->at( 3 ) )
            ->method( 'get' )
            ->with( 'something' )
            ->will( $this->returnValue( $fakeService1 ) );
        $this->container
            ->expects( $this->at( 4 ) )
            ->method( 'set' )
            ->with( 'something', $fakeService1 );

        $fakeService2 = new \stdClass;
        $this->container
            ->expects( $this->at( 5 ) )
            ->method( 'set' )
            ->with( 'something_else', null );
        $this->container
            ->expects( $this->at( 6 ) )
            ->method( 'get' )
            ->with( 'something_else' )
            ->will( $this->returnValue( $fakeService2 ) );
        $this->container
            ->expects( $this->at( 7 ) )
            ->method( 'set' )
            ->with( 'something_else', $fakeService2 );

        $listener = new DynamicSettingsListener( $resettableServices, $dynamicSettingsServiceIds );
        $listener->setContainer( $this->container );
        $listener->onConfigScopeChange( $event );
        $this->assertSame( $siteAccess, $event->getSiteAccess() );
    }
}
