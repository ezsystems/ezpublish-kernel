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
    /**
     * @var \eZ\Publish\API\Repository\ObjectStateService
     */
    protected $innerService;

    public function __construct(ObjectStateService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function createObjectStateGroup(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct)
    {
        $this->innerService->createObjectStateGroup($objectStateGroupCreateStruct);
    }

    public function loadObjectStateGroup(
        $objectStateGroupId,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadObjectStateGroup($objectStateGroupId, $prioritizedLanguages);
    }

    public function loadObjectStateGroups(
        $offset = 0,
        $limit = -1,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadObjectStateGroups($offset, $limit, $prioritizedLanguages);
    }

    public function loadObjectStates(
        ObjectStateGroup $objectStateGroup,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadObjectStates($objectStateGroup, $prioritizedLanguages);
    }

    public function updateObjectStateGroup(
        ObjectStateGroup $objectStateGroup,
        ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct
    ) {
        $this->innerService->updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);
    }

    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup)
    {
        $this->innerService->deleteObjectStateGroup($objectStateGroup);
    }

    public function createObjectState(
        ObjectStateGroup $objectStateGroup,
        ObjectStateCreateStruct $objectStateCreateStruct
    ) {
        $this->innerService->createObjectState($objectStateGroup, $objectStateCreateStruct);
    }

    public function loadObjectState(
        $stateId,
        array $prioritizedLanguages = []
    ) {
        $this->innerService->loadObjectState($stateId, $prioritizedLanguages);
    }

    public function updateObjectState(
        ObjectState $objectState,
        ObjectStateUpdateStruct $objectStateUpdateStruct
    ) {
        $this->innerService->updateObjectState($objectState, $objectStateUpdateStruct);
    }

    public function setPriorityOfObjectState(
        ObjectState $objectState,
        $priority
    ) {
        $this->innerService->setPriorityOfObjectState($objectState, $priority);
    }

    public function deleteObjectState(ObjectState $objectState)
    {
        $this->innerService->deleteObjectState($objectState);
    }

    public function setContentState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup,
        ObjectState $objectState
    ) {
        $this->innerService->setContentState($contentInfo, $objectStateGroup, $objectState);
    }

    public function getContentState(
        ContentInfo $contentInfo,
        ObjectStateGroup $objectStateGroup
    ) {
        $this->innerService->getContentState($contentInfo, $objectStateGroup);
    }

    public function getContentCount(ObjectState $objectState)
    {
        $this->innerService->getContentCount($objectState);
    }

    public function newObjectStateGroupCreateStruct($identifier)
    {
        $this->innerService->newObjectStateGroupCreateStruct($identifier);
    }

    public function newObjectStateGroupUpdateStruct()
    {
        $this->innerService->newObjectStateGroupUpdateStruct();
    }

    public function newObjectStateCreateStruct($identifier)
    {
        $this->innerService->newObjectStateCreateStruct($identifier);
    }

    public function newObjectStateUpdateStruct()
    {
        $this->innerService->newObjectStateUpdateStruct();
    }
}
