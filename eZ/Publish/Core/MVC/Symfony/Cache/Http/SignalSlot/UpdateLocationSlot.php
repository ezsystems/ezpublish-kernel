<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling UpdateLocationSignal.
 *
 * @todo Signal missing info on parent location, which is relevant if priority of location was updated.
 */
class UpdateLocationSlot extends AbstractContentSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\UpdateLocationSignal;
    }
}
