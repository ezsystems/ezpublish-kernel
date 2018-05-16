<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Tests;

use eZ\Publish\API\Repository\BookmarkService as APIBookmarkService;
use eZ\Publish\API\Repository\Values\Bookmark\BookmarkList;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\SignalSlot\BookmarkService;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\CreateBookmarkSignal;
use eZ\Publish\Core\SignalSlot\Signal\BookmarkService\DeleteBookmarkSignal;
use eZ\Publish\Core\SignalSlot\SignalDispatcher;

class BookmarkServiceTest extends ServiceTest
{
    public function serviceProvider()
    {
        $location = new Location([
            'id' => 60,
        ]);

        return [
            [
                'createBookmark',
                [$location],
                null,
                1,
                CreateBookmarkSignal::class,
                [
                    'locationId' => $location->id,
                ],
            ],
            [
                'deleteBookmark',
                [$location],
                null,
                1,
                DeleteBookmarkSignal::class,
                [
                    'locationId' => $location->id,
                ],
            ],
            [
                'loadBookmarks',
                [0, 25],
                new BookmarkList(),
                0,
            ],
            [
                'isBookmarked',
                [$location],
                false,
                0,
            ],
        ];
    }

    protected function getServiceMock()
    {
        return $this->createMock(APIBookmarkService::class);
    }

    protected function getSignalSlotService($innerService, SignalDispatcher $dispatcher)
    {
        return new BookmarkService($innerService, $dispatcher);
    }
}
