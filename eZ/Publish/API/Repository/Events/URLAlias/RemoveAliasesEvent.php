<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URLAlias;

use eZ\Publish\API\Repository\Events\AfterEvent;

interface RemoveAliasesEvent extends AfterEvent
{
    public function getAliasList(): array;
}
