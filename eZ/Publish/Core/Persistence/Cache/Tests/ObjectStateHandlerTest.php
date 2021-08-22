<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler as SPIObjectStateHandler;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group as SPIObjectStateGroup;
use eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct as SPIInputStruct;

/**
 * Test case for Persistence\Cache\ObjectStateHandler.
 */
class ObjectStateHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'objectStateHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIObjectStateHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, string? $key
        return [
            ['createGroup', [new SPIInputStruct()], [['state_group_all', [], true]], [], 'ez-sga'],
            ['updateGroup', [5, new SPIInputStruct()], [['state_group', [5], false]], ['sg-5']],
            ['deleteGroup', [5], [['state_group', [5], false]], ['sg-5']],
            ['create', [5, new SPIInputStruct()], [['state_list_by_group', [5], true]], [], 'ez-slbg-5'],
            ['update', [7, new SPIInputStruct()], [['state', [7], false]], ['s-7']],
            ['setPriority', [7, 99], [['state', [7], false]], ['s-7']],
            ['delete', [7], [['state', [7], false]], ['s-7']],
            ['setContentState', [4, 5, 7], [['state_by_group_on_content', [5, 4], true]], [], 'ez-sbg-5-oc-4'],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $group = new SPIObjectStateGroup(['id' => 5]);
        $state = new SPIObjectState(['id' => 7]);

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tagGeneratorResults, string $key, mixed? $data
        return [
            ['loadGroup', [5], 'ez-sg-5', [['state_group', [], true]], ['ez-sg'], $group],
            [
                'loadGroupByIdentifier',
                ['lock'],
                'ez-sg-lock-bi',
                [
                    ['state_group', [], true],
                    ['by_identifier_suffix', [], false],
                ],
                ['ez-sg', '-bi'],
                $group
            ],
            ['loadAllGroups', [], 'ez-sga', [['state_group_all', [], true]], ['ez-sga'], [$group]],
            ['loadObjectStates', [5], 'ez-slbg-5', [['state_list_by_group', [], true]], ['ez-slbg'], [$state]],
            ['load', [7], 'ez-s-7', [['state', [], true]], ['ez-s'], $state],
            [
                'loadByIdentifier',
                ['lock', 5],
                'ez-si-lock-bg-5',
                [
                    ['state_identifier', [], true],
                    ['by_group', [5], false],
                ],
                ['ez-si', 'bg-5'],
                $state
            ],
            [
                'getContentState',
                [4, 5],
                'ez-sbg-5-oc-4',
                [
                    ['state_by_group', [], true],
                    ['on_content', [4], false],
                ],
                ['ez-sbg', 'oc-4'],
                $state
            ],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $group = new SPIObjectStateGroup(['id' => 5]);
        $state = new SPIObjectState(['id' => 7]);

        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tagGeneratorResults, string $key, mixed? $data
        return [
            [
                'loadGroup',
                [5],
                'ez-sg-5',
                [
                    ['state_group', [], true],
                    ['state_group', [5], false],
                ],
                ['ez-sg', 'sg-5'],
                $group
            ],
            [
                'loadGroupByIdentifier',
                ['lock'],
                'ez-sg-lock-bi',
                [
                    ['state_group', [], true],
                    ['by_identifier_suffix', [], false],
                    ['state_group', [5], false],
                ],
                ['ez-sg', '-bi', 'sg-5'],
                $group
            ],
            [
                'loadAllGroups',
                [],
                'ez-sga',
                [
                    ['state_group_all', [], true],
                    ['state_group', [5], false],
                ],
                ['ez-sga', 'sg-5'],
                [$group]
            ],
            [
                'loadObjectStates',
                [5],
                'ez-slbg-5',
                [
                    ['state_list_by_group', [], true],
                    ['state_group', [5], false],
                    ['state', [7], false],
                ],
                ['ez-slbg', 'sg-5', 's-7'],
                [$state]
            ],
            [
                'load',
                [7],
                'ez-s-7',
                [
                    ['state', [], true],
                    ['state', [7], false],
                    ['state_group', [null], false],
                ],
                ['ez-s', 's-7', 'sg-null'],
                $state
            ],
            [
                'loadByIdentifier',
                ['lock', 5],
                'ez-si-lock-bg-5',
                [
                    ['state_identifier', [], true],
                    ['by_group', [5], false],
                    ['state', [7], false],
                    ['state_group', [null], false],
                ],
                ['ez-si', 'bg-5', 's-7', 'sg-null'],
                $state
            ],
            [
                'getContentState',
                [4, 5],
                'ez-sbg-5-oc-4',
                [
                    ['state_by_group', [], true],
                    ['on_content', [4], false],
                    ['state', [7], false],
                    ['content', [4], false],
                ],
                ['ez-sbg', 'oc-4', 's-7', 'c-4'],
                $state
            ],
        ];
    }
}
