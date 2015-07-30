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
use eZ\Publish\Core\MVC\Symfony\Cache\Http\EventListener\RelatedLocationsListener;
use eZ\Publish\Core\MVC\Symfony\Event\ContentCacheClearEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit_Framework_TestCase;

class RelatedLocationsListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $repository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contentService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $locationService;

    /**
     * @var RelatedLocationsListener
     */
    private $listener;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = $this
            ->getMockBuilder('\eZ\Publish\Core\Repository\Repository')
            ->disableOriginalConstructor()
            ->setMethods(['getContentService', 'getLocationService'])
            ->getMock();
        $this->contentService = $this->getMock('\eZ\Publish\API\Repository\ContentService');
        $this->locationService = $this->getMock('\eZ\Publish\API\Repository\LocationService');
        $this->repository
            ->expects($this->any())
            ->method('getContentService')
            ->will($this->returnValue($this->contentService));
        $this->repository
            ->expects($this->any())
            ->method('getLocationService')
            ->will($this->returnValue($this->locationService));

        $this->listener = new RelatedLocationsListener($this->repository);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [MVCEvents::CACHE_CLEAR_CONTENT => ['onContentCacheClear', 100]],
            RelatedLocationsListener::getSubscribedEvents()
        );
    }

    public function testOnContentCacheClear()
    {
        $contentId = 123;
        $contentInfo = new ContentInfo(['id' => $contentId]);
        $event = new ContentCacheClearEvent($contentInfo);

        $versionInfo = new VersionInfo();
        $this->contentService
            ->expects($this->once())
            ->method('loadVersionInfo')
            ->with($contentInfo)
            ->will($this->returnValue($versionInfo));

        // Relation
        $relatedContentInfo1 = new ContentInfo(['id' => 1]);
        $relatedLocation1 = new Location();
        $relatedContentInfo2 = new ContentInfo(['id' => 2]);
        $relatedLocation2 = new Location();
        $relatedLocation3 = new Location();
        $relations = [
            new Relation(['destinationContentInfo' => $relatedContentInfo1]),
            new Relation(['destinationContentInfo' => $relatedContentInfo2]),
        ];
        $this->contentService
            ->expects($this->once())
            ->method('loadRelations')
            ->with($versionInfo)
            ->will($this->returnValue($relations));

        // Reverse relations
        $reverseRelatedContentInfo = new ContentInfo();
        $relatedLocation4 = new Location();
        $reverseRelations = [new Relation(['sourceContentInfo' => $reverseRelatedContentInfo])];
        $this->contentService
            ->expects($this->once())
            ->method('loadReverseRelations')
            ->with($contentInfo)
            ->will($this->returnValue($reverseRelations));

        // Relation locations loading with locationService
        $this->locationService
            ->expects($this->exactly(count($relations) + count($reverseRelations)))
            ->method('loadLocations')
            ->will(
                $this->returnValueMap(
                    [
                        [$relatedContentInfo1, null, [$relatedLocation1]],
                        [$relatedContentInfo2, null, [$relatedLocation2, $relatedLocation3]],
                        [$reverseRelatedContentInfo, null, [$relatedLocation4]],
                    ]
                )
            );

        $allRelatedLocations = [$relatedLocation1, $relatedLocation2, $relatedLocation3, $relatedLocation4];
        $this->listener->onContentCacheClear($event);
        $this->assertSame($allRelatedLocations, $event->getLocationsToClear());
    }
}
