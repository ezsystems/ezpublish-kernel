<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPILocationHandler;

/**
 * Test case for Persistence\Cache\LocationHandler.
 */
class LocationHandlerTest extends AbstractInMemoryCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'locationHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPILocationHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key, mixed? $returnValue
        return [
            ['copySubtree', [12, 45]],
            ['move', [12, 45], ['location-path-12']],
            ['markSubtreeModified', [12]],
            ['hide', [12], ['location-path-data-12']],
            ['unHide', [12], ['location-path-data-12']],
            ['swap', [12, 45], ['location-12', 'location-45']],
            ['update', [new UpdateStruct(), 12], ['location-data-12']],
            ['create', [new CreateStruct(['contentId' => 4, 'mainLocationId' => true])], ['content-4', 'role-assignment-group-list-4']],
            ['create', [new CreateStruct(['contentId' => 4, 'mainLocationId' => false])], ['content-4', 'role-assignment-group-list-4']],
            ['removeSubtree', [12], ['location-path-12']],
            ['setSectionForSubtree', [12, 2], ['location-path-12']],
            ['changeMainLocation', [4, 12], ['content-4']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $location = new Location(['id' => 12]);

        // string $method, array $arguments, string $key, mixed? $data, bool? $multi = false
        return [
            ['load', [12], 'ez-location-12-1', $location],
            ['load', [12, ['eng-GB', 'bra-PG'], false], 'ez-location-12-bra-PG|eng-GB|0', $location],
            ['loadList', [[12]], 'ez-location-12-1', [12 => $location], true],
            ['loadList', [[12], ['eng-GB', 'bra-PG'], false], 'ez-location-12-bra-PG|eng-GB|0', [12 => $location], true],
            ['loadSubtreeIds', [12], 'ez-location-subtree-12', [33, 44]],
            ['loadLocationsByContent', [4, 12], 'ez-content-locations-4-root-12', [$location]],
            ['loadLocationsByContent', [4], 'ez-content-locations-4', [$location]],
            ['loadParentLocationsForDraftContent', [4], 'ez-content-locations-4-parentForDraft', [$location]],
            ['loadByRemoteId', ['34fe5y4'], 'ez-location-remoteid-34fe5y4-1', $location],
            ['loadByRemoteId', ['34fe5y4', ['eng-GB', 'arg-ES']], 'ez-location-remoteid-34fe5y4-arg-ES|eng-GB|1', $location],
        ];
    }
}
