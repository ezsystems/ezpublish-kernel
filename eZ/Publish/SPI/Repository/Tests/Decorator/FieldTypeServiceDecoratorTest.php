<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\SPI\Repository\Decorator\FieldTypeServiceDecorator;

class FieldTypeServiceDecoratorTest extends TestCase
{
    protected function createDecorator(FieldTypeService $service): FieldTypeService
    {
        return new class($service) extends FieldTypeServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(FieldTypeService::class);
    }

    public function testGetFieldTypesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('getFieldTypes')->with(...$parameters);

        $decoratedService->getFieldTypes(...$parameters);
    }

    public function testGetFieldTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce0eda66.08473991'];

        $serviceMock->expects($this->exactly(1))->method('getFieldType')->with(...$parameters);

        $decoratedService->getFieldType(...$parameters);
    }

    public function testHasFieldTypeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce0edab1.24451920'];

        $serviceMock->expects($this->exactly(1))->method('hasFieldType')->with(...$parameters);

        $decoratedService->hasFieldType(...$parameters);
    }
}
