<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\SignalSlot\Signal\UserPreferenceService;

use eZ\Publish\Core\SignalSlot\Signal;

class UserPreferenceSetSignal extends Signal
{
    /** @var string */
    public $name;

    /** @var string */
    public $value;
}
