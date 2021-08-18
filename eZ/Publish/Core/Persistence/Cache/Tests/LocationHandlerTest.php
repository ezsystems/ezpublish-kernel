<?php

/**
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
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, array? $key, mixed? $returnValue
        return [
            ['copySubtree', [12, 45]],
            ['move', [12, 45], [['location_path', [12], false]], ['lp-12']],
            ['markSubtreeModified', [12]],
            ['hide', [12], [['location_path', [12], false]], ['lp-12']],
            ['unHide', [12], [['location_path', [12], false]], ['lp-12']],
            [
                'swap',
                [12, 45],
                [
                    ['location', [12], false],
                    ['location', [45], false],
                ],
                ['l-12', 'l-45']
            ],
            ['update', [new UpdateStruct(), 12], [['location', [12], false]], ['l-12']],
            [
                'create',
                [new CreateStruct(['contentId' => 4, 'mainLocationId' => true])],
                [
                    ['content', [4], false],
                    ['role_assignment_group_list', [4], false],
                ],
                ['c-4', 'ragl-4']
            ],
            [
                'create',
                [new CreateStruct(['contentId' => 4, 'mainLocationId' => false])],
                [
                    ['content', [4], false],
                    ['role_assignment_group_list', [4], false],
                ],
                ['c-4', 'ragl-4']
            ],
            ['removeSubtree', [12], [['location_path', [12], false]], ['lp-12']],
            ['setSectionForSubtree', [12, 2], [['location_path', [12], false]], ['lp-12']],
            ['changeMainLocation', [4, 12], [['content', [4], false]], ['c-4']],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $location = new Location(['id' => 12]);

        // string $method, array $arguments, string $key, mixed? $data, bool? $multi = false
        return [
            ['load', [12], 'ez-l-12-1', $location],
            ['load', [12, ['eng-GB', 'bra-PG'], false], 'ez-l-12-bra-PG|eng-GB|0', $location],
            ['loadList', [[12]], 'ez-l-12-1', [12 => $location], true],
            ['loadList', [[12], ['eng-GB', 'bra-PG'], false], 'ez-l-12-bra-PG|eng-GB|0', [12 => $location], true],
            ['loadSubtreeIds', [12], 'ez-ls-12', [33, 44]],
            ['loadLocationsByContent', [4, 12], 'ez-cl-4-root-12', [$location]],
            ['loadLocationsByContent', [4], 'ez-cl-4', [$location]],
            ['loadParentLocationsForDraftContent', [4], 'ez-cl-4-pfd', [$location]],
            ['loadByRemoteId', ['34fe5y4'], 'ez-lri-34fe5y4-1', $location],
            ['loadByRemoteId', ['34fe5y4', ['eng-GB', 'arg-ES']], 'ez-lri-34fe5y4-arg-ES|eng-GB|1', $location],
        ];
    }
}
