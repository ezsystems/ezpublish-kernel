<?php

/**
 * File containing the TrashServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\TrashService as APITrashService;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult;
use eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResultList;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\TrashService;
use eZ\Publish\Core\SignalSlot\Signal\TrashService as TrashServiceSignals;

class TrashServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APITrashService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new TrashService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $newParentLocationId = 2;
        $trashItemId = $locationId = 60;
        $contentId = 59;
        $trashItemContentInfo = $this->getContentInfo($contentId, md5('trash'));
        $trashItemParentLocationId = 17;

        $trashItem = new TrashItem(
            [
                'id' => $trashItemId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $trashItemParentLocationId,
            ]
        );

        $newParentLocation = new Location(
            [
                'id' => $newParentLocationId,
                'contentInfo' => $this->getContentInfo(53, md5('root')),
            ]
        );

        $location = new Location(
            [
                'id' => $locationId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $trashItemParentLocationId,
            ]
        );

        $locationWithNewParent = new Location(
            [
                'id' => $locationId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $newParentLocationId,
            ]
        );

        $trashItemDeleteResult = new TrashItemDeleteResult(
            [
                'trashItemId' => $trashItemId,
                'contentId' => $contentId,
                'contentRemoved' => true,
            ]
        );

        $trashItemDeleteResultList = new TrashItemDeleteResultList(
            [
                'items' => [$trashItemDeleteResult],
            ]
        );

        return [
            [
                'loadTrashItem',
                [$trashItemId],
                $trashItem,
                0,
            ],
            [
                'trash',
                [$location],
                $trashItem,
                1,
                TrashServiceSignals\TrashSignal::class,
                ['locationId' => $locationId],
            ],
            [
                'recover',
                [$trashItem, $newParentLocation],
                $locationWithNewParent,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal',
                [
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $newParentLocationId,
                    'newLocationId' => $locationId,
                ],
            ],
            [
                'recover',
                [$trashItem, null],
                $location,
                1,
                TrashServiceSignals\RecoverSignal::class,
                [
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $trashItemParentLocationId,
                    'newLocationId' => $locationId,
                ],
            ],
            [
                'emptyTrash',
                [],
                $trashItemDeleteResultList,
                1,
                TrashServiceSignals\EmptyTrashSignal::class,
                ['trashItemDeleteResultList' => $trashItemDeleteResultList],
            ],
            [
                'deleteTrashItem',
                [$trashItem],
                $trashItemDeleteResult,
                1,
                TrashServiceSignals\DeleteTrashItemSignal::class,
                [
                    'trashItemId' => $trashItemId,
                    'trashItemDeleteResult' => $trashItemDeleteResult,
                ],
            ],
            [
                'findTrashItems',
                [new Query()],
                new SearchResult(['totalCount' => 0]),
                0,
            ],
        ];
    }
}
