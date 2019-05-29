<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Bookmark;

final class BookmarkEvents
{
    public const CREATE_BOOKMARK = CreateBookmarkEvent::NAME;
    public const BEFORE_CREATE_BOOKMARK = BeforeCreateBookmarkEvent::NAME;
    public const DELETE_BOOKMARK = DeleteBookmarkEvent::NAME;
    public const BEFORE_DELETE_BOOKMARK = BeforeDeleteBookmarkEvent::NAME;
}
