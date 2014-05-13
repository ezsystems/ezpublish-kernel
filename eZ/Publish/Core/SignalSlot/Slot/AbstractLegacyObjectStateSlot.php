<?php
/**
 * File containing the Legacy\AbstractLegacyObjectStateSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function ()
            {
                // Passing null as $cacheItem parameter is not used by this method
                eZCache::clearStateLimitations( null );
            },
            false
        );
    }
}
