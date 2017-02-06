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
 * A slot handling SwapLocationSignal.
 */
class SwapLocationSlot extends AbstractContentSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal $signal
     */
    protected function generateTags(Signal $signal)
    {
        return [
            'location-' . $signal->location1Id,
            'parent-' . $signal->location1Id,
            'location-' . $signal->location2Id,
            'parent-' . $signal->location2Id,
        ];
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\SwapLocationSignal;
    }
}
