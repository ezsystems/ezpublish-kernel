<?php

/**
 * File containing the LocationServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\Core\Repository\Values\Content\Location;
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

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new LocationService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $rootId = 2;
        $rootPath = '/1/2';
        $rootContentId = 57;
        $rootContentRemoteId = md5('content root');
        $rootRemoteId = md5('root');
        $locationId = 60;
        $locationPath = '/1/2/60';
        $locationContentId = 59;
        $locationContentRemoteId = md5('not content root');
        $locationRemoteId = md5('not root');

        $rootContentInfo = $this->getContentInfo($rootContentId, $rootContentRemoteId);
        $root = new Location(
            array(
                'id' => $rootId,
                'path' => $rootPath,
                'remoteId' => $rootRemoteId,
                'contentInfo' => $rootContentInfo,
                'parentLocationId' => 1,
            )
        );
        $locationContentInfo = $this->getContentInfo($locationContentId, $locationContentRemoteId);
        $location = new Location(
            array(
                'id' => $locationId,
                'path' => $locationPath,
                'remoteId' => $locationRemoteId,
                'contentInfo' => $locationContentInfo,
                'parentLocationId' => $rootId,
            )
        );

        $rootChildren = new LocationList(
            array(
                'totalCount' => 1,
                'locations' => array($location),
            )
        );

        $locationCreateStruct = new LocationCreateStruct();
        $locationUpdateStruct = new LocationUpdateStruct();

        return array(
            array(
                'copySubtree',
                array($location, $root),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal',
                array(
                    'subtreeId' => $locationId,
                    'targetParentLocationId' => $rootId,
                ),
            ),
            array(
                'loadLocation',
                array($rootId),
                $root,
                0,
            ),
            array(
                'loadLocationByRemoteId',
                array($rootRemoteId),
                $root,
                0,
            ),
            array(
                'loadLocations',
                array($locationContentInfo, $root),
                array($location),
                0,
            ),
            array(
                'loadLocationChildren',
                array($root, 0, 1),
                $rootChildren,
                0,
            ),
            array(
                'getLocationChildCount',
                array($root),
                1,
                0,
            ),
            array(
                'createLocation',
                array($locationContentInfo, $locationCreateStruct),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CreateLocationSignal',
                array(
                    'contentId' => $locationContentId,
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ),
            ),
            array(
                'updateLocation',
                array($location, $locationUpdateStruct),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UpdateLocationSignal',
                array(
                    'contentId' => $locationContentId,
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ),
            ),
            array(
                'swapLocation',
                array($location, $root),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal',
                array(
                    'location1Id' => $locationId,
                    'content1Id' => $locationContentId,
                    'parentLocation1Id' => $rootId,
                    'location2Id' => $rootId,
                    'content2Id' => $rootContentId,
                    'parentLocation2Id' => 1,
                ),
            ),
            array(
                'hideLocation',
                array($location),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal',
                array(
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ),
            ),
            array(
                'unhideLocation',
                array($location),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UnhideLocationSignal',
                array(
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ),
            ),
            array(
                'moveSubtree',
                array($location, $root),
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal',
                array(
                    'locationId' => $locationId,
                    'newParentLocationId' => $rootId,
                    'oldParentLocationId' => $rootId,
                ),
            ),
            array(
                'deleteLocation',
                array($location),
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal',
                array(
                    'locationId' => $locationId,
                    'contentId' => $locationContentId,
                    'parentLocationId' => $rootId,
                ),
            ),
            array(
                'newLocationCreateStruct',
                array($rootId),
                $locationCreateStruct,
                0,
            ),
            array(
                'newLocationUpdateStruct',
                array(),
                $locationUpdateStruct,
                0,
            ),
        );
    }
}
