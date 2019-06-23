<?php

/**
 * File containing the TrashServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\TrashService;

class TrashServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\TrashService'
        );
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new TrashService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $newParentLocationId = 2;
        $trashItemId = $locationId = 60;
        $trashItemContentInfo = $this->getContentInfo(59, md5('trash'));
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
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal',
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
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal',
                [
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $trashItemParentLocationId,
                    'newLocationId' => $locationId,
                ],
            ],
            [
                'emptyTrash',
                [],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\EmptyTrashSignal',
                [],
            ],
            [
                'deleteTrashItem',
                [$trashItem],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\DeleteTrashItemSignal',
                ['trashItemId' => $trashItemId],
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
