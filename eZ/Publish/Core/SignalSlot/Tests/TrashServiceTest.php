<?php
/**
 * File containing the TrashServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\Content\TrashItem;
use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
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

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new TrashService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $rootId = 2;
        $trashItemId = $locationId = 60;
        $trashItemContentInfo = $this->getContentInfo( 59, md5( 'trash' ) );

        $trashItem = new TrashItem(
            array(
                'id' => $trashItemId,
                'contentInfo' => $trashItemContentInfo
            )
        );

        $location = new Location(
            array(
                'id' => $locationId,
                'contentInfo' => $trashItemContentInfo
            )
        );
        $root = new Location(
            array(
                'id' => $rootId,
                'contentInfo' => $this->getContentInfo( 53, md5( 'root' ) )
            )
        );

        return array(
            array(
                'loadTrashItem',
                array( $trashItemId ),
                $trashItem,
                0
            ),
            array(
                'trash',
                array( $location ),
                $trashItem,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\TrashSignal',
                array( 'locationId' => $locationId )
            ),
            array(
                'recover',
                array( $trashItem, $root ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\RecoverSignal',
                array(
                    'trashItemId' => $trashItemId,
                    'newParentLocationId' => $rootId,
                    'newLocationId' => $locationId,
                )
            ),
            array(
                'emptyTrash',
                array(),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\EmptyTrashSignal',
                array()
            ),
            array(
                'deleteTrashItem',
                array( $trashItem ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\TrashService\DeleteTrashItemSignal',
                array( 'trashItemId' => $trashItemId )
            ),
            array(
                'findTrashItems',
                array( new Query ),
                new SearchResult( array( 'totalCount' => 0 ) ),
                0
            ),
        );
    }
}
