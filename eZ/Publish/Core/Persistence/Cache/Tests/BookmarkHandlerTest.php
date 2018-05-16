<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Tests\Core\Persistence\Cache;

use eZ\Publish\Core\Persistence\Cache\Tests\AbstractCacheHandlerTest;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use eZ\Publish\SPI\Persistence\Bookmark\CreateStruct;
use eZ\Publish\SPI\Persistence\Bookmark\Handler as SPIBookmarkHandler;

/**
 * Test case for Persistence\Cache\BookmarkHandler.
 */
class BookmarkHandlerTest extends AbstractCacheHandlerTest
{
    public function getHandlerMethodName(): string
    {
        return 'bookmarkHandler';
    }

    public function getHandlerClassName(): string
    {
        return SPIBookmarkHandler::class;
    }

    public function providerForUnCachedMethods(): array
    {
        // string $method, array $arguments, array? $tags, string? $key, mixed? $returnValue
        return [
            ['create', [new CreateStruct()], null, null, new Bookmark()],
            ['delete', [1], ['bookmark-1']],
            ['loadUserBookmarks', [3, 2, 1], null, null, []],
            ['countUserBookmarks', [3], null, null, 1],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $bookmark = new Bookmark([
            'id' => 1,
            'locationId' => 2,
            'userId' => 3,
        ]);

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['loadByUserIdAndLocationId', [3, [2]], 'ez-bookmark-3-2', [2 => $bookmark], true],
        ];
    }
}
