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
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as SPILocationHandler;

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
            ['locationSwapped', [1, 2], null, null],
        ];
    }

    public function providerForCachedLoadMethods(): array
    {
        $bookmark = new Bookmark([
            'id' => 1,
            'locationId' => 43,
            'userId' => 3,
        ]);

        $calls = [['locationHandler', SPILocationHandler::class, 'load', new Location(['pathString' => '/1/2/43/'])]];

        // string $method, array $arguments, string $key, mixed? $data
        return [
            ['loadByUserIdAndLocationId', [3, [43]], 'ez-bookmark-3-43', [43 => $bookmark], true, $calls],
        ];
    }
}
