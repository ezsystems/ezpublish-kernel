<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\UserPreferenceService;
use eZ\Publish\SPI\Repository\Decorator\UserPreferenceServiceDecorator;

class UserPreferenceServiceDecoratorTest extends TestCase
{
    protected function createDecorator(UserPreferenceService $service): UserPreferenceService
    {
        return new class($service) extends UserPreferenceServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(UserPreferenceService::class);
    }

    public function testSetUserPreferenceDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [['random_value_5ced05ce1437c3.99987071']];

        $serviceMock->expects($this->exactly(1))->method('setUserPreference')->with(...$parameters);

        $decoratedService->setUserPreference(...$parameters);
    }

    public function testGetUserPreferenceDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce143830.91322594'];

        $serviceMock->expects($this->exactly(1))->method('getUserPreference')->with(...$parameters);

        $decoratedService->getUserPreference(...$parameters);
    }

    public function testLoadUserPreferencesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            360,
            922,
        ];

        $serviceMock->expects($this->exactly(1))->method('loadUserPreferences')->with(...$parameters);

        $decoratedService->loadUserPreferences(...$parameters);
    }

    public function testGetUserPreferenceCountDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('getUserPreferenceCount')->with(...$parameters);

        $decoratedService->getUserPreferenceCount(...$parameters);
    }
}
