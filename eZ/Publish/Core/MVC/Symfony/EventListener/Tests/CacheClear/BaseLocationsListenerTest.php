<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\EventListener\Tests\CacheClear;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\EventListener\CacheClear\BaseLocationsListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class BaseLocationsListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var BaseLocationsListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->locationService = $this->getMock( '\eZ\Publish\API\Repository\LocationService' );
        $this->listener = new BaseLocationsListener( $this->locationService );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]],
            BaseLocationsListener::getSubscribedEvents()
        );
    }

    public function testOnContentCacheClear()
    {
        $contentId = 123;
        $contentInfo = new ContentInfo( ['id' => $contentId] );
        $event = new ContentCacheClearEvent( $contentInfo );

        $locations = [
            new Location(),
            new Location(),
            new Location(),
            new Location(),
        ];
        $this->locationService
            ->expects( $this->once() )
            ->method( 'loadLocations' )
            ->with( $contentInfo )
            ->will( $this->returnValue( $locations ) );

        $this->listener->onContentCacheClear( $event );
        $this->assertSame( $locations, $event->getLocationsToClear() );
    }
}
