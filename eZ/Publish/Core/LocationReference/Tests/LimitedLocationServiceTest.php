<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests;

use eZ\Publish\Core\LocationReference\LimitedLocationService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;

final class LimitedLocationServiceTest extends TestCase
{
    private const LOCATION_ID = 54;
    private const LOCATION_REMOTE_ID = 'babe4a915b1dd5d369e79adb9d6c0c6a';
    private const LOCATION_PATH = '/1/2/54/';

    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit\Framework\MockObject\MockObject */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
    private $expectedLocation;

    /** @var \eZ\Publish\Core\LocationReference\LimitedLocationService */
    private $limitedLocationService;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
        $this->expectedLocation = $this->createMock(Location::class);
        $this->limitedLocationService = new LimitedLocationService($this->locationService);
    }

    public function testLoadLocation(): void
    {
        $this->locationService
            ->method('loadLocation')
            ->with(self::LOCATION_ID)
            ->willReturn($this->expectedLocation);

        $this->assertEquals(
            $this->expectedLocation,
            $this->limitedLocationService->loadLocation(self::LOCATION_ID)
        );
    }

    public function testLocationByRemoteId(): void
    {
        $this->locationService
            ->method('loadLocationByRemoteId')
            ->with(self::LOCATION_REMOTE_ID)
            ->willReturn($this->expectedLocation);

        $this->assertEquals(
            $this->expectedLocation,
            $this->limitedLocationService->loadLocationByRemoteId(self::LOCATION_REMOTE_ID)
        );
    }

    public function testLocationByPathString(): void
    {
        $this->locationService
            ->method('loadLocation')
            ->with(self::LOCATION_ID)
            ->willReturn($this->expectedLocation);

        $this->assertEquals(
            $this->expectedLocation,
            $this->limitedLocationService->loadLocationByPathString(self::LOCATION_PATH)
        );
    }

    public function testParentLocation(): void
    {
        $location = $this->createMock(Location::class);
        $location
            ->method('__get')
            ->with('parentLocationId')
            ->willReturn(self::LOCATION_ID);

        $this->locationService
            ->method('loadLocation')
            ->with(self::LOCATION_ID)
            ->willReturn($this->expectedLocation);

        $this->assertEquals(
            $this->expectedLocation,
            $this->limitedLocationService->loadParentLocation($location)
        );
    }
}
