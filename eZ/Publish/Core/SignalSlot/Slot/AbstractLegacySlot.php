<?php
/**
 * File containing the AbstractLegacySlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
     * Executes a legacy kernel callback
     *
     * Does the callback with both post-reinitialize and formtoken checks disabled.
     *
     * @param callable $callback
     *
     * @return mixed
     */
    protected function runLegacyKernelCallback( $callback )
    {
        // Initialize legacy kernel if not already done
        if ( $this->legacyKernel instanceof Closure )
        {
            $legacyKernelClosure = $this->legacyKernel;
            $this->legacyKernel = $legacyKernelClosure();
        }

        return $this->legacyKernel->runCallback(
            $callback,
            false,
            false
        );
    }
}
