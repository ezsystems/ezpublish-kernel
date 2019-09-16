<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests;

use eZ\Publish\Core\LocationReference\LocationReference;
use eZ\Publish\Core\LocationReference\LocationReferenceResolverInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Values\Content\Location;
use PHPUnit\Framework\TestCase;
use Throwable;

final class LocationReferenceTest extends TestCase
{
    private const EXAMPLE_REFERENCE = 'remote_id("babe4a915b1dd5d369e79adb9d6c0c6a")';

    public function testGetLocation(): void
    {
        $location = $this->createMock(Location::class);

        $reference = new LocationReference(
            $this->createResolverMockForValidReference(self::EXAMPLE_REFERENCE, $location),
            self::EXAMPLE_REFERENCE
        );

        $this->assertEquals($location, $reference->getLocation());
    }

    public function testGetLocationOrNullReturnsLocation(): void
    {
        $location = $this->createMock(Location::class);

        $reference = new LocationReference(
            $this->createResolverMockForValidReference(self::EXAMPLE_REFERENCE, $location),
            self::EXAMPLE_REFERENCE
        );

        $this->assertEquals($location, $reference->getLocationOrNull());
    }

    /**
     * @dataProvider dataProviderForExceptionsThrownByLoadLocation
     */
    public function testGetLocationOrNullReturnsNull(Throwable $exception): void
    {
        $reference = new LocationReference(
            $this->createResolverMockForInvalidReference(self::EXAMPLE_REFERENCE, $exception),
            self::EXAMPLE_REFERENCE
        );

        $this->assertNull($reference->getLocationOrNull());
    }

    public function testGetLocationOrDefaultReturnsLocation(): void
    {
        $location = $this->createMock(Location::class);
        $fallback = $this->createMock(Location::class);

        $reference = new LocationReference(
            $this->createResolverMockForValidReference(self::EXAMPLE_REFERENCE, $location),
            self::EXAMPLE_REFERENCE
        );

        $this->assertEquals($location, $reference->getLocationOrDefault($fallback));
    }

    /**
     * @dataProvider dataProviderForExceptionsThrownByLoadLocation
     */
    public function testGetLocationOrDefaultReturnsDefault(Throwable $exception): void
    {
        $fallback = $this->createMock(Location::class);

        $reference = new LocationReference(
            $this->createResolverMockForInvalidReference(self::EXAMPLE_REFERENCE, $exception),
            self::EXAMPLE_REFERENCE
        );

        $this->assertEquals($fallback, $reference->getLocationOrDefault($fallback));
    }

    public function dataProviderForExceptionsThrownByLoadLocation(): array
    {
        return [
            [$this->createMock(NotFoundException::class)],
            [$this->createMock(UnauthorizedException::class)],
        ];
    }

    private function createResolverMockForValidReference(string $reference, Location $location): LocationReferenceResolverInterface
    {
        $locationReferenceResolver = $this->createMock(LocationReferenceResolverInterface::class);
        $locationReferenceResolver
            ->method('resolve')
            ->with($reference)
            ->willReturn($location);

        return $locationReferenceResolver;
    }

    private function createResolverMockForInvalidReference(string $reference, Throwable $exception): LocationReferenceResolverInterface
    {
        $locationReferenceResolver = $this->createMock(LocationReferenceResolverInterface::class);
        $locationReferenceResolver
            ->method('resolve')
            ->with($reference)
            ->willThrowException($exception);

        return $locationReferenceResolver;
    }
}
