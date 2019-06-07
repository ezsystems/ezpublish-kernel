<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\TrashService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\TrashItem;
use eZ\Publish\SPI\Repository\Decorator\TrashServiceDecorator;

class TrashServiceDecoratorTest extends TestCase
{
    protected function createDecorator(MockObject $service): TrashService
    {
        return new class($service) extends TrashServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(TrashService::class);
    }

    public function testLoadTrashItemDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce15c270.43306350'];

        $serviceMock->expects($this->exactly(1))->method('loadTrashItem')->with(...$parameters);

        $decoratedService->loadTrashItem(...$parameters);
    }

    public function testTrashDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Location::class)];

        $serviceMock->expects($this->exactly(1))->method('trash')->with(...$parameters);

        $decoratedService->trash(...$parameters);
    }

    public function testRecoverDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(TrashItem::class),
            $this->createMock(Location::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('recover')->with(...$parameters);

        $decoratedService->recover(...$parameters);
    }

    public function testEmptyTrashDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $serviceMock->expects($this->exactly(1))->method('emptyTrash')->with();

        $decoratedService->emptyTrash();
    }

    public function testDeleteTrashItemDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(TrashItem::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteTrashItem')->with(...$parameters);

        $decoratedService->deleteTrashItem(...$parameters);
    }

    public function testFindTrashItemsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(Query::class)];

        $serviceMock->expects($this->exactly(1))->method('findTrashItems')->with(...$parameters);

        $decoratedService->findTrashItems(...$parameters);
    }
}
