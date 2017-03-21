<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\ObjectState as SPIObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Handler;
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
        return Handler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['createGroup', [new SPIInputStruct()], [], 'ez-state-group-all'],
            ['updateGroup', [5, new SPIInputStruct()], ['state-group-5']],
            ['deleteGroup', [5], ['state-group-5']],
            ['create', [5, new SPIInputStruct()], [], 'ez-state-list-by-group-5'],
            ['update', [7, new SPIInputStruct()], ['state-7']],
            ['setPriority', [7, 99], ['state-7']],
            ['delete', [7], ['state-7']],
            ['setContentState', [4, 5, 7], [], 'ez-state-by-group-5-on-content-4'],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $group = new SPIObjectStateGroup(['id' => 5]);
        $state = new SPIObjectState(['id' => 7]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['loadGroup', [5], 'ez-state-group-5', $group],
            ['loadGroupByIdentifier', ['lock'], 'ez-state-group-lock-by-identifier', $group],
            ['loadAllGroups', [], 'ez-state-group-all', [$group]],
            ['loadObjectStates', [5], 'ez-state-list-5-by-group', [$state]],
            ['load', [7], 'ez-state-7', $state],
            ['loadByIdentifier', ['lock', 5], 'ez-state-identifier-lock-by-group-5', $state],
            ['getContentState', [4, 5], 'ez-state-by-group-5-on-content-4', $state],
        ];
    }
}
