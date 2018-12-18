<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests;

use eZ\Publish\API\Repository\LocationService as APIService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
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
            ['copySubtree', [$location, $location]],

            ['getLocationChildCount', [$location]],

            ['createLocation', [$contentInfo, $locationCreateStruct]],

            ['updateLocation', [$location, $locationUpdateStruct]],

            ['swapLocation', [$location, $location]],

            ['hideLocation', [$location]],

            ['unhideLocation', [$location]],

            ['moveSubtree', [$location, $location]],

            ['deleteLocation', [$location], false],

            ['newLocationCreateStruct', [55]],

            ['newLocationUpdateStruct', []],
        ];
    }

    public function providerForLanguagesLookupMethods()
    {
        $location = new Location();
        $contentInfo = new ContentInfo();
        $versionInfo = new VersionInfo();

        // string $method, array $arguments, mixed|null $return, int $languageArgumentIndex, ?callable $callback, ?int $alwaysAvailableArgumentIndex
        return [
            ['loadLocation', [55], true, 1],
            ['loadLocation', [55, self::LANG_ARG], true, 1],
            ['loadLocation', [55, self::LANG_ARG, true], true, 1, null, 2],

            ['loadLocationList', [[55]], [55 => $location], 1],
            ['loadLocationList', [[55], self::LANG_ARG], [55 => $location], 1],
            ['loadLocationList', [[55], self::LANG_ARG, true], [55 => $location], 1, null, 2],

            ['loadLocationByRemoteId', ['ergemiotregf'], true, 1],
            ['loadLocationByRemoteId', ['ergemiotregf', self::LANG_ARG], true, 1],
            ['loadLocationByRemoteId', ['ergemiotregf', self::LANG_ARG, true], true, 1, null, 2],

            ['loadLocations', [$contentInfo, null], true, 2],
            ['loadLocations', [$contentInfo, $location, self::LANG_ARG], true, 2],

            ['loadLocationChildren', [$location, 0, 15], true, 3],
            ['loadLocationChildren', [$location, 50, 50, self::LANG_ARG], true, 3],

            ['loadParentLocationsForDraftContent', [$versionInfo], true, 1],
            ['loadParentLocationsForDraftContent', [$versionInfo, self::LANG_ARG], true, 1],
        ];
    }
}
