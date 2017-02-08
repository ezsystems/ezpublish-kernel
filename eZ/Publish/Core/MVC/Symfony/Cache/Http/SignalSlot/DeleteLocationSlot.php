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
 * A slot handling DeleteLocationSignal.
 */
class DeleteLocationSlot extends AbstractContentSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal $signal
     */
    protected function generateTags(Signal $signal)
    {
        $tags = parent::generateTags($signal);
        $tags[] = 'path-' . $signal->locationId;

        return $tags;
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\DeleteLocationSignal;
    }
}
