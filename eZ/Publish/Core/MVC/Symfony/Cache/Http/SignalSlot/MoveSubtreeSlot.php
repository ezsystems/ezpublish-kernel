<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling MoveSubtreeSignal.
 *
 * @todo FIXME what is this one supposed to clear ?
 */
class MoveSubtreeSlot extends AbstractSlot
{
    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal $signal
     */
    protected function extractContentId( Signal $signal )
    {
        // @todo Will fail
        return null;
    }

    protected function supports( Signal $signal )
    {
        return $signal instanceof Signal\LocationService\MoveSubtreeSignal;
    }
}
