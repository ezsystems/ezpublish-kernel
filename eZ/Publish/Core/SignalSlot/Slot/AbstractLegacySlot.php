<?php
/**
 * File containing the AbstractLegacySlot class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Slot;
use Closure;

/**
 * A abstract legacy slot covering common functions needed for legacy slots.
 */
abstract class AbstractLegacySlot extends Slot
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @param \Closure $legacyKernelClosure
     */
    public function __construct( Closure $legacyKernelClosure )
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
    }

    /**
     * Returns the legacy kernel object.
     *
     * @return \ezpKernelHandler
     */
    protected function getLegacyKernel()
    {
        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }
}
