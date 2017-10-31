<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Helper\Tests;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\Helper\PreviewLocationProvider;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPILocationHandler;
use PHPUnit\Framework\TestCase;

class PreviewLocationProviderTest extends TestCase
{
    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit_Framework_MockObject_MockObject */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit_Framework_MockObject_MockObject */
    private $locationService;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler|\PHPUnit_Framework_MockObject_MockObject */
    private $locationHandler;

    /** @var \eZ\Publish\Core\Helper\PreviewLocationProvider */
    private $provider;

    protected function setUp()
    {
        parent::setUp();

        $this->contentService = $this->createMock(ContentService::class);
        $this->locationService = $this->createMock(LocationService::class);
        $this->locationHandler = $this->createMock(SPILocationHandler::class);
        $this->provider = new PreviewLocationProvider($this->locationService, $this->contentService, $this->locationHandler);
    }

    public function testGetPreviewLocationDraft()
    {
        $contentId = 123;
        $parentLocationId = 456;

        $contentInfo = $this
            ->getMockBuilder(APIContentInfo::class)
            ->setConstructorArgs(array(array('id' => $contentId)))
            ->getMockForAbstractClass();

        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));

        $this->locationService
            ->expects($this->never())
            ->method('loadLocation');

        $this->locationHandler
            ->expects($this->once())
            ->method('loadParentLocationsForDraftContent')
            ->with($contentId)
            ->will($this->returnValue(array(new Location(array('id' => $parentLocationId)))));

        $location = $this->provider->loadMainLocation($contentId);
        $this->assertInstanceOf(APILocation::class, $location);
        $this->assertSame($contentInfo, $location->contentInfo);
        $this->assertNull($location->id);
        $this->assertEquals($parentLocationId, $location->parentLocationId);
    }

    public function testGetPreviewLocation()
    {
        $contentId = 123;
        $locationId = 456;
        $contentInfo = $this
            ->getMockBuilder(APIContentInfo::class)
            ->setConstructorArgs(array(array('id' => $contentId, 'mainLocationId' => $locationId)))
            ->getMockForAbstractClass();
        $location = $this
            ->getMockBuilder(Location::class)
            ->setConstructorArgs(array(array('id' => $locationId, 'contentInfo' => $contentInfo)))
            ->getMockForAbstractClass();
        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));
        $this->locationService
            ->expects($this->once())
            ->method('loadLocation')
            ->with($locationId)
            ->will($this->returnValue($location));
        $this->locationHandler->expects($this->never())->method('loadParentLocationsForDraftContent');

        $returnedLocation = $this->provider->loadMainLocation($contentId);
        $this->assertSame($location, $returnedLocation);
        $this->assertSame($contentInfo, $returnedLocation->contentInfo);
    }

    public function testGetPreviewLocationNoLocation()
    {
        $contentId = 123;

        $contentInfo = $this
            ->getMockBuilder(APIContentInfo::class)
            ->setConstructorArgs(array(array('id' => $contentId)))
            ->getMockForAbstractClass();
        $this->contentService
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($contentId)
            ->will($this->returnValue($contentInfo));
        $this->locationHandler
            ->expects($this->once())
            ->method('loadParentLocationsForDraftContent')
            ->with($contentId)
            ->will($this->returnValue(array()));

        $this->locationHandler->expects($this->never())->method('loadLocationsByContent');

        $this->assertNull($this->provider->loadMainLocation($contentId));
    }
}
