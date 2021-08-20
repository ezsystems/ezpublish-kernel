<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tests;

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
        // string $method, array $arguments, array? $tagGeneratorArguments, array? $tags, string? $key, mixed? $returnValue
        return [
            ['create', [new CreateStruct()], null, null, null, new Bookmark()],
            ['delete', [1], [['bookmark', [1], false]], ['b-1']],
            ['loadUserBookmarks', [3, 2, 1], null, null, null, []],
            ['countUserBookmarks', [3], null, null, null, 1],
            ['locationSwapped', [1, 2], null, null, null],
        ];
    }

    public function providerForCachedLoadMethodsHit(): array
    {
        $bookmark = new Bookmark([
            'id' => 1,
            'locationId' => 43,
            'userId' => 3,
        ]);

        $calls = [['locationHandler', SPILocationHandler::class, 'load', new Location(['pathString' => '/1/2/43/'])]];

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            ['loadByUserIdAndLocationId', [3, [43]], 'ez-b-3-43', ['bookmark', [3], true], ['ez-b-3'], [43 => $bookmark], true, $calls],
        ];
    }

    public function providerForCachedLoadMethodsMiss(): array
    {
        $bookmark = new Bookmark([
            'id' => 1,
            'locationId' => 43,
            'userId' => 3,
        ]);

        $calls = [['locationHandler', SPILocationHandler::class, 'load', new Location(['pathString' => '/1/2/43/'])]];

        // string $method, array $arguments, string $key, array? $tagGeneratorArguments, array? $tagGeneratorResults, mixed? $data
        return [
            [
                'loadByUserIdAndLocationId',
                [3, [43]],
                'ez-b-3-43',
                [
                    ['bookmark', [3], true],
                    ['bookmark', [1], false],
                    ['location', [43], false],
                    ['user', [3], false],
                    ['location_path', [1], false],
                    ['location_path', [2], false],
                    ['location_path', [43], false],
                ],
                ['ez-b-3', 'b-1', 'l-43', 'u-3', 'lp-1', 'lp-2', 'lp-43'],
                [43 => $bookmark],
                true,
                $calls
            ],
        ];
    }
}
