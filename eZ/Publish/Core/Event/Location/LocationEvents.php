<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Location;

final class LocationEvents
{
    public const COPY_SUBTREE = CopySubtreeEvent::NAME;
    public const BEFORE_COPY_SUBTREE = BeforeCopySubtreeEvent::NAME;
    public const CREATE_LOCATION = CreateLocationEvent::NAME;
    public const BEFORE_CREATE_LOCATION = BeforeCreateLocationEvent::NAME;
    public const UPDATE_LOCATION = UpdateLocationEvent::NAME;
    public const BEFORE_UPDATE_LOCATION = BeforeUpdateLocationEvent::NAME;
    public const SWAP_LOCATION = SwapLocationEvent::NAME;
    public const BEFORE_SWAP_LOCATION = BeforeSwapLocationEvent::NAME;
    public const HIDE_LOCATION = HideLocationEvent::NAME;
    public const BEFORE_HIDE_LOCATION = BeforeHideLocationEvent::NAME;
    public const UNHIDE_LOCATION = UnhideLocationEvent::NAME;
    public const BEFORE_UNHIDE_LOCATION = BeforeUnhideLocationEvent::NAME;
    public const MOVE_SUBTREE = MoveSubtreeEvent::NAME;
    public const BEFORE_MOVE_SUBTREE = BeforeMoveSubtreeEvent::NAME;
    public const DELETE_LOCATION = DeleteLocationEvent::NAME;
    public const BEFORE_DELETE_LOCATION = BeforeDeleteLocationEvent::NAME;
}
