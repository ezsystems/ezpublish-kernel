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
        // string $method, array $arguments, array? $tagGeneratingArguments, array? $keyGeneratingArguments, array? $tags, array? $key, ?mixed $returnValue
        return [
            ['copySubtree', [12, 45]],
            ['move', [12, 45], [['location_path', [12], false]], null, ['lp-12']],
            ['markSubtreeModified', [12]],
            ['hide', [12], [['location_path', [12], false]], null, ['lp-12']],
            ['unHide', [12], [['location_path', [12], false]], null, ['lp-12']],
            [
                'swap',
                [12, 45],
                [
                    ['location', [12], false],
                    ['location', [45], false],
                ],
                null,
                ['l-12', 'l-45'],
            ],
            ['update', [new UpdateStruct(), 12], [['location', [12], false]], null, ['l-12']],
            [
                'create',
                [new CreateStruct(['contentId' => 4, 'mainLocationId' => true])],
                [
                    ['content', [4], false],
                    ['role_assignment_group_list', [4], false],
                ],
                null,
                ['c-4', 'ragl-4'],
            ],
            [
                'create',
                [new CreateStruct(['contentId' => 4, 'mainLocationId' => false])],
                [
                    ['content', [4], false],
                    ['role_assignment_group_list', [4], false],
                ],
                null,
                ['c-4', 'ragl-4'],
            ],
            ['removeSubtree', [12], [['location_path', [12], false]], null, ['lp-12']],
            ['setSectionForSubtree', [12, 2], [['location_path', [12], false]], null, ['lp-12']],
            ['changeMainLocation', [4, 12], [['content', [4], false]], null, ['c-4']],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $location = new Location(['id' => 12]);

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            ['load', [12], 'ibx-l-12-1', null, null, [['location', [], true]], ['ibx-l'], $location],
            ['load', [12, ['eng-GB', 'bra-PG'], false], 'ibx-l-12-bra-PG|eng-GB|0', null, null, [['location', [], true]], ['ibx-l'], $location],
            ['loadList', [[12]], 'ibx-l-12-1', null, null, [['location', [], true]], ['ibx-l'], [12 => $location], true],
            ['loadList', [[12], ['eng-GB', 'bra-PG'], false], 'ibx-l-12-bra-PG|eng-GB|0', null, null, [['location', [], true]], ['ibx-l'], [12 => $location], true],
            ['loadSubtreeIds', [12], 'ibx-ls-12', null, null, [['location_subtree', [], true]], ['ibx-ls'], [33, 44]],
            [
                'loadLocationsByContent',
                [4, 12],
                'ibx-cl-4-root-12',
                [
                    ['content', [4], false],
                    ['location', [12], false],
                    ['location_path', [12], false],
                ],
                ['c-4', 'l-12', 'lp-12'],
                [
                    ['content_locations', [], true],
                ],
                ['ibx-cl'],
                [$location],
            ],
            [
                'loadLocationsByContent',
                [4],
                'ibx-cl-4',
                [
                    ['content', [4], false],
                ],
                ['c-4'],
                [
                    ['content_locations', [], true],
                ],
                ['ibx-cl'],
                [$location],
            ],
            [
                'loadParentLocationsForDraftContent',
                [4],
                'ibx-cl-4-pfd',
                null,
                null,
                [
                    ['content_locations', [], true],
                    ['parent_for_draft_suffix', [], false],
                ],
                ['ibx-cl', '-pfd'],
                [$location],
            ],
            ['loadByRemoteId', ['34fe5y4'], 'ibx-lri-34fe5y4-1', null, null, [['location_remote_id', [], true]], ['ibx-lri'], $location],
            ['loadByRemoteId', ['34fe5y4', ['eng-GB', 'arg-ES']], 'ibx-lri-34fe5y4-arg-ES|eng-GB|1', null, null, [['location_remote_id', [], true]], ['ibx-lri'], $location],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $location = new Location(
            [
                'id' => 12,
                'contentId' => 15,
                'pathString' => '/1/2',
            ]
        );

        // string $method, array $arguments, string $key, array? $tagGeneratingArguments, array? $tagGeneratingResults, array? $keyGeneratingArguments, array? $keyGeneratingResults, mixed? $data, bool $multi
        return [
            [
                'load',
                [12],
                'ibx-l-12-1',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location', [], true],
                ],
                ['ibx-l'],
                $location,
            ],
            [
                'load',
                [12, ['eng-GB', 'bra-PG'], false],
                'ibx-l-12-bra-PG|eng-GB|0',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location', [], true],
                ],
                ['ibx-l'],
                $location,
            ],
            [
                'loadList',
                [[12]],
                'ibx-l-12-1',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location', [], true],
                ],
                ['ibx-l'],
                [12 => $location],
                true,
            ],
            [
                'loadList',
                [[12],
                ['eng-GB', 'bra-PG'], false, ],
                'ibx-l-12-bra-PG|eng-GB|0',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location', [], true],
                ],
                ['ibx-l'],
                [12 => $location],
                true,
            ],
            [
                'loadSubtreeIds',
                [12],
                'ibx-ls-12',
                [
                    ['location', [12], false],
                    ['location_path', [12], false],
                    ['location', [33], false],
                    ['location_path', [33], false],
                    ['location', [44], false],
                    ['location_path', [44], false],
                ],
                ['l-12', 'lp-12', 'l-33', 'lp-33', 'l-44', 'lp-44'],
                [
                    ['location_subtree', [], true],
                ],
                ['ibx-ls'],
                [33, 44],
            ],
            [
                'loadLocationsByContent',
                [4, 12],
                'ibx-cl-4-root-12',
                [
                    ['content', [4], false],
                    ['location', [12], false],
                    ['location_path', [12], false],
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-4', 'l-12', 'lp-12', 'c-15', 'l-12', 'lp-2'],
                [
                    ['content_locations', [], true],
                ],
                ['ibx-cl'],
                [$location],
            ],
            [
                'loadLocationsByContent',
                [4],
                'ibx-cl-4',
                [
                    ['content', [4], false],
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-4', 'c-15', 'l-12', 'lp-2'],
                [
                    ['content_locations', [], true],
                ],
                ['ibx-cl'],
                [$location],
            ],
            [
                'loadParentLocationsForDraftContent',
                [4],
                'ibx-cl-4-pfd',
                [
                    ['content', [4], false],
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-4', 'c-15', 'l-12', 'lp-2'],
                [
                    ['content_locations', [], true],
                    ['parent_for_draft_suffix', [], false],
                ],
                ['ibx-cl', '-pfd'],
                [$location],
            ],
            [
                'loadByRemoteId',
                ['34fe5y4'],
                'ibx-lri-34fe5y4-1',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location_remote_id', [], true],
                ],
                ['ibx-lri'],
                $location,
            ],
            [
                'loadByRemoteId',
                ['34fe5y4', ['eng-GB', 'arg-ES']],
                'ibx-lri-34fe5y4-arg-ES|eng-GB|1',
                [
                    ['content', [15], false],
                    ['location', [12], false],
                    ['location_path', ['2'], false],
                ],
                ['c-15', 'l-12', 'lp-2'],
                [
                    ['location_remote_id', [], true],
                ],
                ['ibx-lri'],
                $location,
            ],
        ];
    }
}
