<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Trash;

final class TrashEvents
{
    public const TRASH = TrashEvent::NAME;
    public const BEFORE_TRASH = BeforeTrashEvent::NAME;
    public const RECOVER = RecoverEvent::NAME;
    public const BEFORE_RECOVER = BeforeRecoverEvent::NAME;
    public const EMPTY_TRASH = EmptyTrashEvent::NAME;
    public const BEFORE_EMPTY_TRASH = BeforeEmptyTrashEvent::NAME;
    public const DELETE_TRASH_ITEM = DeleteTrashItemEvent::NAME;
    public const BEFORE_DELETE_TRASH_ITEM = BeforeDeleteTrashItemEvent::NAME;
}
