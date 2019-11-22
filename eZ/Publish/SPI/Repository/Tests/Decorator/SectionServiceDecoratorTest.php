<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
    private const EXAMPLE_SECTION_ID = 1;

    protected function createDecorator(MockObject $service): SectionService
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

        $serviceMock->expects($this->once())->method('createSection')->with(...$parameters);

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

        $serviceMock->expects($this->once())->method('updateSection')->with(...$parameters);

        $decoratedService->updateSection(...$parameters);
    }

    public function testLoadSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [self::EXAMPLE_SECTION_ID];

        $serviceMock->expects($this->once())->method('loadSection')->with(...$parameters);

        $decoratedService->loadSection(...$parameters);
    }

    public function testLoadSectionsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('loadSections')->with(...$parameters);

        $decoratedService->loadSections(...$parameters);
    }

    public function testLoadSectionByIdentifierDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce10cd87.67751220'];

        $serviceMock->expects($this->once())->method('loadSectionByIdentifier')->with(...$parameters);

        $decoratedService->loadSectionByIdentifier(...$parameters);
    }

    public function testCountAssignedContentsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->once())->method('countAssignedContents')->with(...$parameters);

        $decoratedService->countAssignedContents(...$parameters);
    }

    public function testIsSectionUsedDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->once())->method('isSectionUsed')->with(...$parameters);

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

        $serviceMock->expects($this->once())->method('assignSection')->with(...$parameters);

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

        $serviceMock->expects($this->once())->method('assignSectionToSubtree')->with(...$parameters);

        $decoratedService->assignSectionToSubtree(...$parameters);
    }

    public function testDeleteSectionDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Section::class)];

        $serviceMock->expects($this->once())->method('deleteSection')->with(...$parameters);

        $decoratedService->deleteSection(...$parameters);
    }

    public function testNewSectionCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newSectionCreateStruct')->with(...$parameters);

        $decoratedService->newSectionCreateStruct(...$parameters);
    }

    public function testNewSectionUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->once())->method('newSectionUpdateStruct')->with(...$parameters);

        $decoratedService->newSectionUpdateStruct(...$parameters);
    }
}
