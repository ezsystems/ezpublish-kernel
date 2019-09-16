<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests\ConfigResolver;

use eZ\Publish\Core\LocationReference\ConfigResolver\LocationConfigResolver;
use eZ\Publish\Core\LocationReference\LocationReference;
use eZ\Publish\Core\LocationReference\LocationReferenceResolverInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

final class LocationConfigResolverTest extends TestCase
{
    private const EXAMPLE_REFERENCE = 'remote_id("babe4a915b1dd5d369e79adb9d6c0c6a")';
    private const EXAMPLE_LOCATION_ID = 2;
    private const EXAMPLE_PARAMETER = ['tree_root', 'content', 'default'];

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $configResolver;

    /** @var \eZ\Publish\Core\LocationReference\LocationReferenceResolverInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $referenceResolver;

    /** @var \eZ\Publish\Core\LocationReference\ConfigResolver\LocationConfigResolver */
    private $locationConfigResolver;

    protected function setUp(): void
    {
        $this->configResolver = $this->createMock(ConfigResolverInterface::class);
        $this->referenceResolver = $this->createMock(LocationReferenceResolverInterface::class);

        $this->locationConfigResolver = new LocationConfigResolver(
            $this->configResolver,
            $this->referenceResolver
        );
    }

    public function testGetLocation(): void
    {
        $location = $this->createMock(Location::class);

        $this->configResolver
            ->method('getParameter')
            ->with(...self::EXAMPLE_PARAMETER)
            ->willReturn(self::EXAMPLE_REFERENCE);

        $this->referenceResolver
            ->method('resolve')
            ->with(self::EXAMPLE_REFERENCE)
            ->willReturn($location);

        $this->assertEquals($location, $this->locationConfigResolver->getLocation(
            ...self::EXAMPLE_PARAMETER
        ));
    }

    public function testGetLocationAcceptLocationId(): void
    {
        $location = $this->createMock(Location::class);

        $this->configResolver
            ->method('getParameter')
            ->with(...self::EXAMPLE_PARAMETER)
            ->willReturn(self::EXAMPLE_LOCATION_ID);

        $this->referenceResolver
            ->method('resolve')
            ->with((string) self::EXAMPLE_LOCATION_ID)
            ->willReturn($location);

        $this->assertEquals($location, $this->locationConfigResolver->getLocation(
            ...self::EXAMPLE_PARAMETER
        ));
    }

    public function testGetLocationReference(): void
    {
        $this->configResolver
            ->method('getParameter')
            ->with(...self::EXAMPLE_PARAMETER)
            ->willReturn(self::EXAMPLE_REFERENCE);

        $reference = $this->locationConfigResolver->getLocationReference(
            ...self::EXAMPLE_PARAMETER
        );

        $this->assertInstanceOf(LocationReference::class, $reference);
        $this->assertEquals(self::EXAMPLE_REFERENCE, (string)$reference);
    }

    public function testGetLocationReferenceAcceptLocationId(): void
    {
        $this->configResolver
            ->method('getParameter')
            ->with(...self::EXAMPLE_PARAMETER)
            ->willReturn(self::EXAMPLE_LOCATION_ID);

        $reference = $this->locationConfigResolver->getLocationReference(
            ...self::EXAMPLE_PARAMETER
        );

        $this->assertInstanceOf(LocationReference::class, $reference);
        $this->assertEquals((string)self::EXAMPLE_LOCATION_ID, (string)$reference);
    }
}
