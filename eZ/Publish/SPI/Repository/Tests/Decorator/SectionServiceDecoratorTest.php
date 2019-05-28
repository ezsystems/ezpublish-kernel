<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;
use eZ\Publish\API\Repository\Values\Content\SectionUpdateStruct;
use eZ\Publish\SPI\Repository\Decorator\SectionServiceDecorator;

class SectionServiceDecoratorTest extends TestCase
{
    protected function createDecorator(SectionService $service): SectionService
    {
        return new class($service) extends SectionServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(SectionService::class);
    }

    public function testCreateSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(SectionCreateStruct::class)];

        $serviceMock->expects($this->exactly(1))->method('createSection')->with(...$parameters);

        $decoratedService->createSection(...$parameters);
    }

    public function testUpdateSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Section::class),
            $this->createMock(SectionUpdateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('updateSection')->with(...$parameters);

        $decoratedService->updateSection(...$parameters);
    }

    public function testLoadSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce10cd25.80094030'];

        $serviceMock->expects($this->exactly(1))->method('loadSection')->with(...$parameters);

        $decoratedService->loadSection(...$parameters);
    }

    public function testLoadSectionsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('loadSections')->with(...$parameters);

        $decoratedService->loadSections(...$parameters);
    }

    public function testLoadSectionByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce10cd87.67751220'];

        $serviceMock->expects($this->exactly(1))->method('loadSectionByIdentifier')->with(...$parameters);

        $decoratedService->loadSectionByIdentifier(...$parameters);
    }

    public function testCountAssignedContentsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->exactly(1))->method('countAssignedContents')->with(...$parameters);

        $decoratedService->countAssignedContents(...$parameters);
    }

    public function testIsSectionUsedDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->exactly(1))->method('isSectionUsed')->with(...$parameters);

        $decoratedService->isSectionUsed(...$parameters);
    }

    public function testAssignSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(Section::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('assignSection')->with(...$parameters);

        $decoratedService->assignSection(...$parameters);
    }

    public function testAssignSectionToSubtreeDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(Location::class),
            $this->createMock(Section::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('assignSectionToSubtree')->with(...$parameters);

        $decoratedService->assignSectionToSubtree(...$parameters);
    }

    public function testDeleteSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteSection')->with(...$parameters);

        $decoratedService->deleteSection(...$parameters);
    }

    public function testNewSectionCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newSectionCreateStruct')->with(...$parameters);

        $decoratedService->newSectionCreateStruct(...$parameters);
    }

    public function testNewSectionUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newSectionUpdateStruct')->with(...$parameters);

        $decoratedService->newSectionUpdateStruct(...$parameters);
    }
}
