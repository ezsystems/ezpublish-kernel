<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\UserPreference;

final class UserPreferenceEvents
{
    public const SET_USER_PREFERENCE = SetUserPreferenceEvent::NAME;
    public const BEFORE_SET_USER_PREFERENCE = BeforeSetUserPreferenceEvent::NAME;
}
