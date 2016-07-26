<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling RecoverSignal.
 */
class RecoverSlot extends PurgeForContentHttpCacheSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\TrashService\RecoverSignal;
    }

    protected function extractLocationIds(Signal $signal)
    {
        return [$signal->newParentLocationId];
    }
}
