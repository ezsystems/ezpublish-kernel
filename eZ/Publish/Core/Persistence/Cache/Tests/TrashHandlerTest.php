<?php

/**
 * File contains Test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache\Tests;

use eZ\Publish\SPI\Persistence\Content\Location\Trash\Handler;

/**
 * Test case for Persistence\Cache\SectionHandler.
 */
class TrashHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'trashHandler';
    }

    public function getHandlerClassName(): string
    {
        return Handler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key
        return [
            ['loadTrashItem', [6]],
            ['trashSubtree', [6], ['location-6', 'location-path-6']],
            ['recover', [6, 2], ['location-6', 'location-path-6']],
            ['emptyTrash', []],
            ['deleteTrashItem', [6]],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        // string $method, array $arguments, string $key, mixed? $data
        return [
        ];
    }
}
