<?php

/**
 * File containing the URLAliasTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\URLAliasService as APIURLAliasService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\URLAliasService;
use eZ\Publish\Core\SignalSlot\Signal\URLAliasService as URLAliasServiceSignals;

class URLAliasServiceTest extends ServiceTest
{
    protected function getServiceMock()
    {
        return $this->createMock(APIURLAliasService::class);
    }

    protected function getSignalSlotService($coreService, SignalDispatcher $dispatcher)
    {
        return new URLAliasService($coreService, $dispatcher);
    }

    public function serviceProvider()
    {
        $locationId = 60;
        $locationPath = '/bugs-bunny';
        $locationRemoteId = md5('bugs bunny');

        $urlAliasId = '42-foobar';
        $globalUrlAliasId = 'rabbit';
        $path = '/lapin';
        $globalPath = '/lapins';
        $globalDestination = '/characters/rabbits';
        $languageCode = 'fre-FR';
        $forward = true;
        $alwaysAvailable = true;

        $contentInfo = $this->getContentInfo(59, md5('bugs bunny contnet'));

        $location = new Location(
            [
                'id' => $locationId,
                'path' => $locationPath,
                'remoteId' => $locationRemoteId,
                'contentInfo' => $contentInfo,
            ]
        );

        $locationUrlAlias = new URLAlias(
            [
                'id' => $urlAliasId,
                'type' => URLAlias::LOCATION,
                'destination' => $locationId,
                'path' => $path,
                'languageCodes' => [$languageCode],
                'forward' => $forward,
            ]
        );

        $globalUrlAlias = new URLAlias(
            [
                'id' => $globalUrlAliasId,
                'type' => URLAlias::RESOURCE,
                'destination' => $globalDestination,
                'path' => $globalPath,
                'languageCodes' => [$languageCode],
                'forward' => $forward,
            ]
        );

        $aliasList = [$globalUrlAlias, $locationUrlAlias];

        return [
            [
                'createUrlAlias',
                [
                    $location, $path, $languageCode, $forward, $alwaysAvailable,
                ],
                $locationUrlAlias,
                1,
                URLAliasServiceSignals\CreateUrlAliasSignal::class,
                ['urlAliasId' => $urlAliasId],
            ],
            [
                'createGlobalUrlAlias',
                [
                    $globalPath,
                    $globalDestination,
                    $languageCode,
                    $forward,
                    $alwaysAvailable,
                ],
                $globalUrlAlias,
                1,
                URLAliasServiceSignals\CreateGlobalUrlAliasSignal::class,
                ['urlAliasId' => $globalUrlAliasId],
            ],
            [
                'listLocationAliases',
                [$location, false, $languageCode, false, []],
                [$locationUrlAlias],
                0,
            ],
            [
                'listGlobalAliases',
                [$languageCode, 1, 2],
                [$globalUrlAlias],
                0,
            ],
            [
                'removeAliases',
                [$aliasList],
                null,
                1,
                URLAliasServiceSignals\RemoveAliasesSignal::class,
                [
                    'aliasList' => $aliasList,
                ],
            ],
            [
                'lookup',
                [$path, $languageCode],
                $locationUrlAlias,
                0,
            ],
            [
                'reverseLookup',
                [$location, $languageCode, false, []],
                $locationUrlAlias,
                0,
            ],
            [
                'load',
                [$urlAliasId],
                $locationUrlAlias,
                0,
            ],
        ];
    }
}
