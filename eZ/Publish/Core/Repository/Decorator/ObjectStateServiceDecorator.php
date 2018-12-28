<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

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
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\ObjectStateService $service
     */
    public function __construct(ObjectStateService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectStateGroup(ObjectStateGroupCreateStruct $objectStateGroupCreateStruct)
    {
        return $this->service->createObjectStateGroup($objectStateGroupCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStateGroup($objectStateGroupId, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStateGroup($objectStateGroupId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStateGroups($offset = 0, $limit = -1, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStateGroups($offset, $limit, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectStates(ObjectStateGroup $objectStateGroup, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectStates($objectStateGroup, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function updateObjectStateGroup(ObjectStateGroup $objectStateGroup, ObjectStateGroupUpdateStruct $objectStateGroupUpdateStruct)
    {
        return $this->service->updateObjectStateGroup($objectStateGroup, $objectStateGroupUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectStateGroup(ObjectStateGroup $objectStateGroup)
    {
        return $this->service->deleteObjectStateGroup($objectStateGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function createObjectState(ObjectStateGroup $objectStateGroup, ObjectStateCreateStruct $objectStateCreateStruct)
    {
        return $this->service->createObjectState($objectStateGroup, $objectStateCreateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function loadObjectState($stateId, array $prioritizedLanguages = [])
    {
        return $this->service->loadObjectState($stateId, $prioritizedLanguages);
    }

    /**
     * {@inheritdoc}
     */
    public function updateObjectState(ObjectState $objectState, ObjectStateUpdateStruct $objectStateUpdateStruct)
    {
        return $this->service->updateObjectState($objectState, $objectStateUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriorityOfObjectState(ObjectState $objectState, $priority)
    {
        return $this->service->setPriorityOfObjectState($objectState, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteObjectState(ObjectState $objectState)
    {
        return $this->service->deleteObjectState($objectState);
    }

    /**
     * {@inheritdoc}
     */
    public function setContentState(ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup, ObjectState $objectState)
    {
        return $this->service->setContentState($contentInfo, $objectStateGroup, $objectState);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentState(ContentInfo $contentInfo, ObjectStateGroup $objectStateGroup)
    {
        return $this->service->getContentState($contentInfo, $objectStateGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentCount(ObjectState $objectState)
    {
        return $this->service->getContentCount($objectState);
    }

    /**
     * {@inheritdoc}
     */
    public function newObjectStateGroupCreateStruct($identifier)
    {
        return $this->service->newObjectStateGroupCreateStruct($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function newObjectStateGroupUpdateStruct()
    {
        return $this->service->newObjectStateGroupUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newObjectStateCreateStruct($identifier)
    {
        return $this->service->newObjectStateCreateStruct($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function newObjectStateUpdateStruct()
    {
        return $this->service->newObjectStateUpdateStruct();
    }
}
