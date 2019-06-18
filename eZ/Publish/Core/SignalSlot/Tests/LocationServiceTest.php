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
            [
                'id' => $rootId,
                'path' => $rootPath,
                'remoteId' => $rootRemoteId,
                'contentInfo' => $rootContentInfo,
                'parentLocationId' => 1,
            ]
        );
        $locationContentInfo = $this->getContentInfo($locationContentId, $locationContentRemoteId);
        $location = new Location(
            [
                'id' => $locationId,
                'path' => $locationPath,
                'remoteId' => $locationRemoteId,
                'contentInfo' => $locationContentInfo,
                'parentLocationId' => $rootId,
            ]
        );

        $rootChildren = new LocationList(
            [
                'totalCount' => 1,
                'locations' => [$location],
            ]
        );

        $locationCreateStruct = new LocationCreateStruct();
        $locationUpdateStruct = new LocationUpdateStruct();

        return [
            [
                'copySubtree',
                [$location, $root],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CopySubtreeSignal',
                [
                    'subtreeId' => $locationId,
                    'targetParentLocationId' => $rootId,
                ],
            ],
            [
                'loadLocation',
                [$rootId],
                $root,
                0,
            ],
            [
                'loadLocationByRemoteId',
                [$rootRemoteId],
                $root,
                0,
            ],
            [
                'loadLocations',
                [$locationContentInfo, $root],
                [$location],
                0,
            ],
            [
                'loadLocationChildren',
                [$root, 0, 1],
                $rootChildren,
                0,
            ],
            [
                'getLocationChildCount',
                [$root],
                1,
                0,
            ],
            [
                'createLocation',
                [$locationContentInfo, $locationCreateStruct],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\CreateLocationSignal',
                [
                    'contentId' => $locationContentId,
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ],
            ],
            [
                'updateLocation',
                [$location, $locationUpdateStruct],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UpdateLocationSignal',
                [
                    'contentId' => $locationContentId,
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ],
            ],
            [
                'swapLocation',
                [$location, $root],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal',
                [
                    'location1Id' => $locationId,
                    'content1Id' => $locationContentId,
                    'parentLocation1Id' => $rootId,
                    'location2Id' => $rootId,
                    'content2Id' => $rootContentId,
                    'parentLocation2Id' => 1,
                ],
            ],
            [
                'hideLocation',
                [$location],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\HideLocationSignal',
                [
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ],
            ],
            [
                'unhideLocation',
                [$location],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\UnhideLocationSignal',
                [
                    'locationId' => $locationId,
                    'parentLocationId' => $rootId,
                ],
            ],
            [
                'moveSubtree',
                [$location, $root],
                $location,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal',
                [
                    'locationId' => $locationId,
                    'newParentLocationId' => $rootId,
                    'oldParentLocationId' => $rootId,
                ],
            ],
            [
                'deleteLocation',
                [$location],
                null,
                1,
                'eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal',
                [
                    'locationId' => $locationId,
                    'contentId' => $locationContentId,
                    'parentLocationId' => $rootId,
                ],
            ],
            [
                'newLocationCreateStruct',
                [$rootId, null],
                $locationCreateStruct,
                0,
            ],
            [
                'newLocationUpdateStruct',
                [],
                $locationUpdateStruct,
                0,
            ],
        ];
    }
}
