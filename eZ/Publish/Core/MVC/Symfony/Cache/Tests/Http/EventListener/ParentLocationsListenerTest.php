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
use eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener\ParentLocationsListener;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Values\Content\Location;
use PHPUnit_Framework_TestCase;

class ParentLocationsListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var ParentLocationsListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->locationService = $this->getMock('\eZ\Publish\API\Repository\LocationService');
        $this->listener = new ParentLocationsListener($this->locationService);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]],
            ParentLocationsListener::getSubscribedEvents()
        );
    }

    public function testOnContentCacheClear()
    {
        $contentId = 123;
        $contentInfo = new ContentInfo(['id' => $contentId]);
        $event = new ContentCacheClearEvent($contentInfo);

        $parentLocationId1 = 456;
        $parentLocationId2 = 789;
        $parentLocationId3 = 234;
        $parentLocationId4 = 567;
        $locations = [
            new Location(['parentLocationId' => $parentLocationId1]),
            new Location(['parentLocationId' => $parentLocationId2]),
            new Location(['parentLocationId' => $parentLocationId3]),
            new Location(['parentLocationId' => $parentLocationId4]),
        ];
        $this->locationService
            ->expects($this->once())
            ->method('loadLocations')
            ->with($contentInfo)
            ->will($this->returnValue($locations));

        $parentLocation1 = new Location(['id' => $parentLocationId1]);
        $parentLocation2 = new Location(['id' => $parentLocationId2]);
        $parentLocation3 = new Location(['id' => $parentLocationId3]);
        $parentLocation4 = new Location(['id' => $parentLocationId4]);
        $parentLocations = [$parentLocation1, $parentLocation2, $parentLocation3, $parentLocation4];
        $this->locationService
            ->expects($this->exactly(count($parentLocations)))
            ->method('loadLocation')
            ->will(
                $this->returnValueMap(
                    [
                        [$parentLocationId1, $parentLocation1],
                        [$parentLocationId2, $parentLocation2],
                        [$parentLocationId3, $parentLocation3],
                        [$parentLocationId4, $parentLocation4],
                    ]
                )
            );

        $this->listener->onContentCacheClear($event);
        $this->assertSame($parentLocations, $event->getLocationsToClear());
    }
}
