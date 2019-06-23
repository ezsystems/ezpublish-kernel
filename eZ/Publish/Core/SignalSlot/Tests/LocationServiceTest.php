<?php

/**
 * File containing the LocationServiceTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\LocationService as APILocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\LocationService;
use eZ\Publish\Core\SignalSlot\Signal\LocationService as LocationServiceSignals;

class LocationServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APILocationService::class);
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
                LocationServiceSignals\CopySubtreeSignal::class,
                [
                    'subtreeId' => $locationId,
                    'targetParentLocationId' => $rootId,
                ],
            ],
            [
                'loadLocation',
                [$rootId, [], true],
                $root,
                0,
            ],
            [
                'loadLocationList',
                [[$rootId], [], true],
                [$root],
                0,
            ],
            [
                'loadLocationByRemoteId',
                [$rootRemoteId, [], true],
                $root,
                0,
            ],
            [
                'loadLocations',
                [$locationContentInfo, $root, []],
                [$location],
                0,
            ],
            [
                'loadLocationChildren',
                [$root, 0, 1, []],
                $rootChildren,
                0,
            ],
            /*array(
                'loadParentLocationsForDraftContent',
                array($root, 0, 1, []),
                $rootChildren,
                0,
            ),*/
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
                LocationServiceSignals\CreateLocationSignal::class,
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
                LocationServiceSignals\UpdateLocationSignal::class,
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
                LocationServiceSignals\SwapLocationSignal::class,
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
                LocationServiceSignals\HideLocationSignal::class,
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
                LocationServiceSignals\UnhideLocationSignal::class,
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
                LocationServiceSignals\MoveSubtreeSignal::class,
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
                LocationServiceSignals\DeleteLocationSignal::class,
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
