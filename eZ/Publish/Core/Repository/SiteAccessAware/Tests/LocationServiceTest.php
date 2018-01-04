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
        $versionInfo = new VersionInfo();
        $locationCreateStruct = new LocationCreateStruct();
        $locationUpdateStruct = new LocationUpdateStruct();

        // string $method, array $arguments, bool $return = true
        return [
            ['copySubtree', [$location, $location]],

            ['loadLocation', [55]],

            ['loadLocationByRemoteId', ['3498jtj943n']],

            ['loadLocations', [$contentInfo]],
            ['loadLocations', [$contentInfo, $location]],

            ['loadLocationChildren', [$location]],
            ['loadLocationChildren', [$location, 50, 50]],

            ['loadParentLocationsForDraftContent', [$versionInfo]],

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
        // string $method, array $arguments, bool $return, int $languageArgumentIndex
        return [];
    }
}
