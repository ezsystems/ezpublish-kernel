<?php
/**
 * File containing the AbstractLegacySlot class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Slot;
use Closure;
use ezpKernelHandler;

/**
 * A abstract legacy slot covering common functions needed for legacy slots.
 */
abstract class AbstractLegacySlot extends Slot
{
    /**
     * @var \Closure|\ezpKernelHandler
     */
    private $legacyKernel;

    /**
     * @param \Closure|\ezpKernelHandler $legacyKernel
     */
    public function __construct( $legacyKernel )
    {
        if ( $legacyKernel instanceof Closure || $legacyKernel instanceof ezpKernelHandler )
            $this->legacyKernel = $legacyKernel;
        else
            throw new \RuntimeException( "Legacy slot only accepts \$legacyKernel instance of Closure or ezpKernelHandler" );
    }

    /**
     * Returns the legacy kernel object.
     *
     * @return \ezpKernelHandler
     */
    protected function getLegacyKernel()
    {
        if ( $this->legacyKernel instanceof ezpKernelHandler )
            return $this->legacyKernel;

        $legacyKernelClosure = $this->legacyKernel;
        return $legacyKernelClosure();
    }
}
