<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Tests\Http\EventListener;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener\AssignedLocationsListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class AssignedLocationsListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var AssignedLocationsListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->locationService = $this->getMock('\eZ\Publish\API\Repository\LocationService');
        $this->listener = new AssignedLocationsListener($this->locationService);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]],
            AssignedLocationsListener::getSubscribedEvents()
        );
    }

    public function testOnContentCacheClear()
    {
        $contentId = 123;
        $contentInfo = new ContentInfo(['id' => $contentId]);
        $event = new ContentCacheClearEvent($contentInfo);

        $locations = [
            new Location(),
            new Location(),
            new Location(),
            new Location(),
        ];
        $this->locationService
            ->expects($this->once())
            ->method('loadLocations')
            ->with($contentInfo)
            ->will($this->returnValue($locations));

        $this->listener->onContentCacheClear($event);
        $this->assertSame($locations, $event->getLocationsToClear());
    }
}
