<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URL;

final class URLEvents
{
    public const UPDATE_URL = UpdateUrlEvent::NAME;
    public const BEFORE_UPDATE_URL = BeforeUpdateUrlEvent::NAME;
}
