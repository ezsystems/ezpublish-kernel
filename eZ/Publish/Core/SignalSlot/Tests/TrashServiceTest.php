<?php

/**
 * File containing the TrashServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\TrashService as APITrashService;
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
        $trashItemContentInfo = $this->getContentInfo(59, md5('trash'));
        $trashItemParentLocationId = 17;

        $trashItem = new TrashItem(
            array(
                'id' => $trashItemId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $trashItemParentLocationId,
            )
        );

        $newParentLocation = new Location(
            array(
                'id' => $newParentLocationId,
                'contentInfo' => $this->getContentInfo(53, md5('root')),
            )
        );

        $location = new Location(
            array(
                'id' => $locationId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $trashItemParentLocationId,
            )
        );

        $locationWithNewParent = new Location(
            array(
                'id' => $locationId,
                'contentInfo' => $trashItemContentInfo,
                'parentLocationId' => $newParentLocationId,
            )
        );

        return array(
            array(
                'loadTrashItem',
                array($trashItemId),
                $trashItem,
                0,
            ),
            array(
                'trash',
                array($location),
                $trashItem,
                1,
                TrashServiceSignals\TrashSignal::class,
                array('locationId' => $locationId),
            ),
            array(
                'recover',
                array($trashItem, $newParentLocation),
                $locationWithNewParent,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal',
                array(
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $newParentLocationId,
                    'newLocationId' => $locationId,
                ),
            ),
            array(
                'recover',
                array($trashItem, null),
                $location,
                1,
                TrashServiceSignals\RecoverSignal::class,
                array(
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $trashItemParentLocationId,
                    'newLocationId' => $locationId,
                ),
            ),
            array(
                'emptyTrash',
                array(),
                null,
                1,
                TrashServiceSignals\EmptyTrashSignal::class,
                array(),
            ),
            array(
                'deleteTrashItem',
                array($trashItem),
                null,
                1,
                TrashServiceSignals\DeleteTrashItemSignal::class,
                array('trashItemId' => $trashItemId),
            ),
            array(
                'findTrashItems',
                array(new Query()),
                new SearchResult(array('totalCount' => 0)),
                0,
            ),
        );
    }
}
