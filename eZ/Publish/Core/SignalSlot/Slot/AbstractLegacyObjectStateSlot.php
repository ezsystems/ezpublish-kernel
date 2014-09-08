<?php
/**
 * File containing the Legacy\AbstractLegacyObjectStateSlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZCache;

/**
 * An abstract legacy slot common for some ObjectStateService signals.
 */
abstract class AbstractLegacyObjectStateSlot extends AbstractLegacySlot
{
    /**
     * Clears object state limitation cache.
     *
     * Concrete implementation of this class should take care of checking the type of the signal.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     */
    public function receive( Signal $signal )
    {
        $this->runLegacyKernelCallback(
            function ()
            {
                // Passing null as $cacheItem parameter is not used by this method
                eZCache::clearStateLimitations( null );
            }
        );
    }
}
