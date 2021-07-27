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
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['createGroup', [new SPIInputStruct()], [], 'ez-sga'],
            ['updateGroup', [5, new SPIInputStruct()], ['sg-5']],
            ['deleteGroup', [5], ['sg-5']],
            ['create', [5, new SPIInputStruct()], [], 'ez-slbg-5'],
            ['update', [7, new SPIInputStruct()], ['s-7']],
            ['setPriority', [7, 99], ['s-7']],
            ['delete', [7], ['s-7']],
            ['setContentState', [4, 5, 7], [], 'ez-sbg-5-oc-4'],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $group = new SPIObjectStateGroup(['id' => 5]);
        $state = new SPIObjectState(['id' => 7]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['loadGroup', [5], 'ez-sg-5', $group],
            ['loadGroupByIdentifier', ['lock'], 'ez-sg-lock-bi', $group],
            ['loadAllGroups', [], 'ez-sga', [$group]],
            ['loadObjectStates', [5], 'ez-slbg-5', [$state]],
            ['load', [7], 'ez-s-7', $state],
            ['loadByIdentifier', ['lock', 5], 'ez-si-lock-bg-5', $state],
            ['getContentState', [4, 5], 'ez-sbg-5-oc-4', $state],
        ];
    }
}
