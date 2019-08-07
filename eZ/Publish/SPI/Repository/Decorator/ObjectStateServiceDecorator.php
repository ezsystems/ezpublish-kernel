<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

use eZ\Publish\API\Repository\ObjectStateService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;

abstract class ObjectStateServiceDecorator implements ObjectStateService
{
    /** @var \eZ\Publish\API\Repository\ObjectStateService */
    protected $innerService;

    public function __construct(ObjectStateService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createObjectStateGroup(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct): ObjectStateGroup
    {
        return $this->innerService->createObjectStateGroup($objectStateGroupCreateStruct);
    }

    public function loadObjectStateGroup(
        int $objectStateGroupId,
        array $prioritizedLanguages = []
    ): ObjectStateGroup {
        return $this->innerService->loadObjectStateGroup($objectStateGroupId, $prioritizedLanguages);
    }

    public function loadObjectStateGroups(
        int $offset = 0,
        int $limit = -1,
        array $prioritizedLanguages = []
    ): iterable {
        return $this->innerService->loadObjectStateGroups($offset, $limit, $prioritizedLanguages);
    }

    public function loadObjectStates(
        ObjectStateGroup $objectStateGroup,
        array $prioritizedLanguages = []
    ): iterable {
        return $this->innerService->loadObjectStates($objectStateGroup, $prioritizedLanguages);
    }

    public function updateObjectStateGroup(
        ObjectStateGroup $objectStateGroup,
        ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
    ): ObjectStateGroup {
        return $this->innerService->updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);
    }

    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup): void
    {
        $this->innerService->deleteObjectStateGroup($objectStateGroup);
    }

    public function createObjectState(
        ObjectStateGroup $objectStateGroup,
        ObjectStateCreateStruct $objectStateCreateStruct
    ): ObjectState {
        return $this->innerService->createObjectState($objectStateGroup, $objectStateCreateStruct);
    }

    public function loadObjectState(
        int $stateId,
        array $prioritizedLanguages = []
    ): ObjectState {
        return $this->innerService->loadObjectState($stateId, $prioritizedLanguages);
    }

    public function updateObjectState(
        ObjectState $objectState,
        ObjectStateUpdateStruct $objectStateUpdateStruct
    ): ObjectState {
        return $this->innerService->updateObjectState($objectState, $objectStateUpdateStruct);
    }

    public function setPriorityOfObjectState(
        ObjectState $objectState,
        int $priority
    ): void {
        $this->innerService->setPriorityOfObjectState($objectState, $priority);
    }

    public function deleteObjectState(ObjectState $objectState): void
    {
        $this->innerService->deleteObjectState($objectState);
    }

    public function setContentState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup,
        ObjectState $objectState
    ): void {
        $this->innerService->setContentState($contentInfo, $objectStateGroup, $objectState);
    }

    public function getContentState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ): ObjectState {
        return $this->innerService->getContentState($contentInfo, $objectStateGroup);
    }

    public function getContentCount(ObjectState $objectState): int
    {
        return $this->innerService->getContentCount($objectState);
    }

    public function newObjectStateGroupCreateStruct(string $identifier): ObjectStateGroupCreateStruct
    {
        return $this->innerService->newObjectStateGroupCreateStruct($identifier);
    }

    public function newObjectStateGroupUpdateStruct(): ObjectStateGroupUpdateStruct
    {
        return $this->innerService->newObjectStateGroupUpdateStruct();
    }

    public function newObjectStateCreateStruct(string $identifier): ObjectStateCreateStruct
    {
        return $this->innerService->newObjectStateCreateStruct($identifier);
    }

    public function newObjectStateUpdateStruct(): ObjectStateUpdateStruct
    {
        return $this->innerService->newObjectStateUpdateStruct();
    }
}
