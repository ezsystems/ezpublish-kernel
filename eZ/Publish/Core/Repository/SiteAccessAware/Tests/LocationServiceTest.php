<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\LocationService as APIService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\Core\Repository\SiteAccessAware\LocationService;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;

class LocationServiceTest extends AbstractServiceTest
{
    public function getAPIServiceClassName()
    {
        return APIService::class;
    }

    public function getSiteAccessAwareServiceClassName()
    {
        return LocationService::class;
    }

    public function providerForPassTroughMethods()
    {
        $location = new Location();
        $contentInfo = new ContentInfo();
        $locationCreateStruct = new LocationCreateStruct();
        $locationUpdateStruct = new LocationUpdateStruct();

        // string $method, array $arguments, bool $return = true
        return [
            ['copySubtree', [$location, $location], $location],

            ['getLocationChildCount', [$location], 100],

            ['createLocation', [$contentInfo, $locationCreateStruct], $location],

            ['updateLocation', [$location, $locationUpdateStruct], $location],

            ['swapLocation', [$location, $location], null],

            ['hideLocation', [$location], $location],

            ['unhideLocation', [$location], $location],

            ['moveSubtree', [$location, $location], null],

            ['deleteLocation', [$location], null],

            ['newLocationCreateStruct', [55], new LocationCreateStruct()],

            ['newLocationUpdateStruct', [], new LocationUpdateStruct()],

            ['getAllLocationsCount', [], 100],
            ['loadAllLocations', [10, 100], [$location]],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $location = new Location();
        $locationList = new LocationList();
        $contentInfo = new ContentInfo();
        $versionInfo = new VersionInfo();

        // string $method, array $arguments, mixed|null $return, int $languageArgumentIndex, ?callable $callback, ?int $alwaysAvailableArgumentIndex
        return [
            ['loadLocation', [55], $location, 1],
            ['loadLocation', [55, self::LANG_ARG], $location, 1],
            ['loadLocation', [55, self::LANG_ARG, true], $location, 1, null, 2],

            ['loadLocationList', [[55]], [55 => $location], 1],
            ['loadLocationList', [[55], self::LANG_ARG], [55 => $location], 1],
            ['loadLocationList', [[55], self::LANG_ARG, true], [55 => $location], 1, null, 2],

            ['loadLocationByRemoteId', ['ergemiotregf'], $location, 1],
            ['loadLocationByRemoteId', ['ergemiotregf', self::LANG_ARG], $location, 1],
            ['loadLocationByRemoteId', ['ergemiotregf', self::LANG_ARG, true], $location, 1, null, 2],

            ['loadLocations', [$contentInfo, null], [$location], 2],
            ['loadLocations', [$contentInfo, $location, self::LANG_ARG], [$location], 2],

            ['loadLocationChildren', [$location, 0, 15], $locationList, 3],
            ['loadLocationChildren', [$location, 50, 50, self::LANG_ARG], $locationList, 3],

            ['loadParentLocationsForDraftContent', [$versionInfo], [$location], 1],
            ['loadParentLocationsForDraftContent', [$versionInfo, self::LANG_ARG], [$location], 1],
        ];
    }
}
