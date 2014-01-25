<?php
/**
 * File containing the LocationServiceTest class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\DomainLogic\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationList;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\LocationService;

class LocationServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->getMock(
            'eZ\\Publish\\API\\Repository\\LocationService'
        );
    }

    protected function getSignalSlotService( $coreService, SignalDispatcher $dispatcher )
    {
        return new LocationService( $coreService, $dispatcher );
    }

    public function serviceProvider()
    {
        $rootId = 2;
        $rootPath = '/1/2';
        $rootContentId = 57;
        $rootContentRemoteId = md5( 'content root' );
        $rootRemoteId = md5( 'root' );
        $locationId = 60;
        $locationPath = '/1/2/60';
        $locationContentId = 59;
        $locationContentRemoteId = md5( 'not content root' );
        $locationRemoteId = md5( 'not root' );

        $rootContentInfo = $this->getContentInfo(
            $rootContentId, $rootContentRemoteId
        );
        $root = new Location(
            array(
                'id' => $rootId,
                'path' => $rootPath,
                'remoteId' => $rootRemoteId,
                'contentInfo' => $rootContentInfo
            )
        );
        $locationContentInfo = $this->getContentInfo(
            $locationContentId, $locationContentRemoteId
        );
        $location = new Location(
            array(
                'id' => $locationId,
                'path' => $locationPath,
                'remoteId' => $locationRemoteId,
                'contentInfo' => $locationContentInfo
            )
        );

        $rootChildren = new LocationList(
            array(
                'totalCount' => 1,
                'locations' => array( $location )
            )
        );

        $locationCreateStruct = new LocationCreateStruct();
        $locationUpdateStruct = new LocationUpdateStruct();

        return array(
            array(
                'copySubtree',
                array( $location, $root ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal',
                array(
                    'subtreeId' => $locationId,
                    'targetParentLocationId' => $rootId
                )
            ),
            array(
                'loadLocation',
                array( $rootId ),
                $root,
                0
            ),
            array(
                'loadLocationByRemoteId',
                array( $rootRemoteId ),
                $root,
                0
            ),
            array(
                'loadLocations',
                array( $locationContentInfo, $root ),
                array( $location ),
                0
            ),
            array(
                'loadLocationChildren',
                array( $root, 0, 1 ),
                $rootChildren,
                0
            ),
            array(
                'getLocationChildCount',
                array( $root ),
                1,
                0
            ),
            array(
                'createLocation',
                array( $locationContentInfo, $locationCreateStruct ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CreateLocationSignal',
                array(
                    'contentId' => $locationContentId,
                    'locationId' => $locationId
                )
            ),
            array(
                'updateLocation',
                array( $location, $locationUpdateStruct ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UpdateLocationSignal',
                array(
                    'contentId' => $locationContentId,
                    'locationId' => $locationId
                )
            ),
            array(
                'swapLocation',
                array( $location, $root ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal',
                array(
                    'location1Id' => $locationId,
                    'content1Id' => $locationContentId,
                    'location2Id' => $rootId,
                    'content2Id' => $rootContentId,
                )
            ),
            array(
                'hideLocation',
                array( $location ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal',
                array(
                    'locationId' => $locationId,
                )
            ),
            array(
                'unhideLocation',
                array( $location ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UnhideLocationSignal',
                array(
                    'locationId' => $locationId,
                )
            ),
            array(
                'moveSubtree',
                array( $location, $root ),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal',
                array(
                    'locationId' => $locationId,
                    'newParentLocationId' => $rootId,
                )
            ),
            array(
                'deleteLocation',
                array( $location ),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal',
                array(
                    'locationId' => $locationId,
                    'contentId' => $locationContentId
                )
            ),
            array(
                'newLocationCreateStruct',
                array( $rootId ),
                $locationCreateStruct,
                0
            ),
            array(
                'newLocationUpdateStruct',
                array(),
                $locationUpdateStruct,
                0
            ),
        );
    }
}
