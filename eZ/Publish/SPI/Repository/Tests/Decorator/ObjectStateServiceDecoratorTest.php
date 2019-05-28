<?php

declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Tests\Decorator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\SPI\Repository\Decorator\ObjectStateServiceDecorator;

class ObjectStateServiceDecoratorTest extends TestCase
{
    protected function createDecorator(ObjectStateService $service): ObjectStateService
    {
        return new class($service) extends ObjectStateServiceDecorator {
        };
    }

    protected function createServiceMock(): MockObject
    {
        return $this->createMock(ObjectStateService::class);
    }

    public function testCreateObjectStateGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ObjectStateGroupCreateStruct::class)];

        $serviceMock->expects($this->exactly(1))->method('createObjectStateGroup')->with(...$parameters);

        $decoratedService->createObjectStateGroup(...$parameters);
    }

    public function testLoadObjectStateGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce167ab2.05518074',
            ['random_value_5ced05ce167af8.71775936'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadObjectStateGroup')->with(...$parameters);

        $decoratedService->loadObjectStateGroup(...$parameters);
    }

    public function testLoadObjectStateGroupsDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce167b48.34907853',
            'random_value_5ced05ce167b50.97498952',
            ['random_value_5ced05ce167b61.83719864'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadObjectStateGroups')->with(...$parameters);

        $decoratedService->loadObjectStateGroups(...$parameters);
    }

    public function testLoadObjectStatesDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            ['random_value_5ced05ce168263.48122762'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadObjectStates')->with(...$parameters);

        $decoratedService->loadObjectStates(...$parameters);
    }

    public function testUpdateObjectStateGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateGroupUpdateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('updateObjectStateGroup')->with(...$parameters);

        $decoratedService->updateObjectStateGroup(...$parameters);
    }

    public function testDeleteObjectStateGroupDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ObjectStateGroup::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteObjectStateGroup')->with(...$parameters);

        $decoratedService->deleteObjectStateGroup(...$parameters);
    }

    public function testCreateObjectStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectStateCreateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('createObjectState')->with(...$parameters);

        $decoratedService->createObjectState(...$parameters);
    }

    public function testLoadObjectStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            'random_value_5ced05ce168f03.95274945',
            ['random_value_5ced05ce168f26.15342671'],
        ];

        $serviceMock->expects($this->exactly(1))->method('loadObjectState')->with(...$parameters);

        $decoratedService->loadObjectState(...$parameters);
    }

    public function testUpdateObjectStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ObjectState::class),
            $this->createMock(ObjectStateUpdateStruct::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('updateObjectState')->with(...$parameters);

        $decoratedService->updateObjectState(...$parameters);
    }

    public function testSetPriorityOfObjectStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ObjectState::class),
            'random_value_5ced05ce169b57.05322524',
        ];

        $serviceMock->expects($this->exactly(1))->method('setPriorityOfObjectState')->with(...$parameters);

        $decoratedService->setPriorityOfObjectState(...$parameters);
    }

    public function testDeleteObjectStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ObjectState::class)];

        $serviceMock->expects($this->exactly(1))->method('deleteObjectState')->with(...$parameters);

        $decoratedService->deleteObjectState(...$parameters);
    }

    public function testSetContentStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
            $this->createMock(ObjectState::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('setContentState')->with(...$parameters);

        $decoratedService->setContentState(...$parameters);
    }

    public function testGetContentStateDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [
            $this->createMock(ContentInfo::class),
            $this->createMock(ObjectStateGroup::class),
        ];

        $serviceMock->expects($this->exactly(1))->method('getContentState')->with(...$parameters);

        $decoratedService->getContentState(...$parameters);
    }

    public function testGetContentCountDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [$this->createMock(ObjectState::class)];

        $serviceMock->expects($this->exactly(1))->method('getContentCount')->with(...$parameters);

        $decoratedService->getContentCount(...$parameters);
    }

    public function testNewObjectStateGroupCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce169c83.55416136'];

        $serviceMock->expects($this->exactly(1))->method('newObjectStateGroupCreateStruct')->with(...$parameters);

        $decoratedService->newObjectStateGroupCreateStruct(...$parameters);
    }

    public function testNewObjectStateGroupUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newObjectStateGroupUpdateStruct')->with(...$parameters);

        $decoratedService->newObjectStateGroupUpdateStruct(...$parameters);
    }

    public function testNewObjectStateCreateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = ['random_value_5ced05ce169cc9.01447563'];

        $serviceMock->expects($this->exactly(1))->method('newObjectStateCreateStruct')->with(...$parameters);

        $decoratedService->newObjectStateCreateStruct(...$parameters);
    }

    public function testNewObjectStateUpdateStructDecorator()
    {
        $serviceMock = $this->createServiceMock();
        $decoratedService = $this->createDecorator($serviceMock);

        $parameters = [];

        $serviceMock->expects($this->exactly(1))->method('newObjectStateUpdateStruct')->with(...$parameters);

        $decoratedService->newObjectStateUpdateStruct(...$parameters);
    }
}
