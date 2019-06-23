<?php

/**
 * File containing the ObjectStateTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\ObjectStateService as APIObjectStateService;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectStateUpdateStruct;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectState;
use eZ\Publish\Core\Repository\Values\ObjectState\ObjectStateGroup;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\ObjectStateService;
use eZ\Publish\Core\SignalSlot\Signal\ObjectStateService as ObjectStateServiceSignals;

class ObjectStateServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIObjectStateService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new ObjectStateService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $objectStateGroupId = 4;
        $objectStateId = 42;
        $priority = 50;
        $contentId = 59;
        $contentRemoteId = md5("What's up doc ?");

        $objectStateGroupCreateStruct = new ObjectStateGroupCreateStruct();
        $objectStateGroupUpdateStruct = new ObjectStateGroupUpdateStruct();
        $objectStateCreateStruct = new ObjectStateCreateStruct();
        $objectStateUpdateStruct = new ObjectStateUpdateStruct();
        $objectStateGroup = new ObjectStateGroup(
            [
                'id' => $objectStateGroupId,
            ]
        );
        $objectState = new ObjectState(
            [
                'id' => $objectStateId,
            ]
        );
        $contentInfo = $this->getContentInfo($contentId, $contentRemoteId);

        return [
            [
                'createObjectStateGroup',
                [$objectStateGroupCreateStruct],
                $objectStateGroup,
                1,
                ObjectStateServiceSignals\CreateObjectStateGroupSignal::class,
                ['objectStateGroupId' => $objectStateGroupId],
            ],
            [
                'loadObjectStateGroup',
                [4, []],
                $objectStateGroup,
                0,
            ],
            [
                'loadObjectStateGroups',
                [1, 1, []],
                [$objectStateGroup],
                0,
            ],
            [
                'loadObjectStates',
                [$objectStateGroup, []],
                [$objectState],
                0,
            ],
            [
                'updateObjectStateGroup',
                [$objectStateGroup, $objectStateGroupUpdateStruct],
                $objectStateGroup,
                1,
                ObjectStateServiceSignals\UpdateObjectStateGroupSignal::class,
                ['objectStateGroupId' => $objectStateGroupId],
            ],
            [
                'deleteObjectStateGroup',
                [$objectStateGroup],
                null,
                1,
                ObjectStateServiceSignals\DeleteObjectStateGroupSignal::class,
                ['objectStateGroupId' => $objectStateGroupId],
            ],
            [
                'createObjectState',
                [$objectStateGroup, $objectStateCreateStruct],
                $objectState,
                1,
                ObjectStateServiceSignals\CreateObjectStateSignal::class,
                [
                    'objectStateGroupId' => $objectStateGroupId,
                    'objectStateId' => $objectStateId,
                ],
            ],
            [
                'loadObjectState',
                [$objectStateId, []],
                $objectState,
                0,
            ],
            [
                'updateObjectState',
                [$objectState, $objectStateUpdateStruct],
                $objectState,
                1,
                ObjectStateServiceSignals\UpdateObjectStateSignal::class,
                [
                    'objectStateId' => $objectStateId,
                ],
            ],
            [
                'setPriorityOfObjectState',
                [$objectState, $priority],
                null,
                1,
                ObjectStateServiceSignals\SetPriorityOfObjectStateSignal::class,
                [
                    'objectStateId' => $objectStateId,
                    'priority' => $priority,
                ],
            ],
            [
                'deleteObjectState',
                [$objectState],
                null,
                1,
                ObjectStateServiceSignals\DeleteObjectStateSignal::class,
                [
                    'objectStateId' => $objectStateId,
                ],
            ],
            [
                'setContentState',
                [$contentInfo, $objectStateGroup, $objectState],
                null,
                1,
                ObjectStateServiceSignals\SetContentStateSignal::class,
                [
                    'objectStateId' => $objectStateId,
                    'contentId' => $contentId,
                    'objectStateGroupId' => $objectStateGroupId,
                ],
            ],
            [
                'getContentState',
                [$contentInfo, $objectStateGroup],
                $objectState,
                0,
            ],
            [
                'getContentCount',
                [$objectState],
                35,
                0,
            ],
            [
                'newObjectStateGroupCreateStruct',
                ['identifier'],
                $objectStateGroupCreateStruct,
                0,
            ],
            [
                'newObjectStateGroupUpdateStruct',
                [],
                $objectStateGroupUpdateStruct,
                0,
            ],
            [
                'newObjectStateUpdateStruct',
                [],
                $objectStateUpdateStruct,
                0,
            ],
            [
                'newObjectStateCreateStruct',
                ['identifier'],
                $objectStateCreateStruct,
                0,
            ],
        ];
    }
}
