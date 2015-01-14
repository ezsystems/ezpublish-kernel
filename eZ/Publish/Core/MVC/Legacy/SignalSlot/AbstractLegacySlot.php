<?php
/**
 * File containing the AbstractLegacySlot class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger;
use eZ\Publish\Core\MVC\Legacy\Cache\Switchable;
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
     * @var \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger
     */
    private $persistenceCacheClearer;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    private $httpCacheClearer;

    /**
     * @param \Closure|\ezpKernelHandler $legacyKernel
     * @param \eZ\Bundle\EzPublishLegacyBundle\Cache\PersistenceCachePurger $persistenceCacheClearer
     * @param \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger $httpCacheClearer
     */
    public function __construct( $legacyKernel, PersistenceCachePurger $persistenceCacheClearer, Switchable $httpCacheClearer )
    {
        if ( $legacyKernel instanceof Closure || $legacyKernel instanceof ezpKernelHandler )
            $this->legacyKernel = $legacyKernel;
        else
            throw new \RuntimeException( "Legacy slot only accepts \$legacyKernel instance of Closure or ezpKernelHandler" );

        $this->persistenceCacheClearer = $persistenceCacheClearer;
        $this->httpCacheClearer = $httpCacheClearer;
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
        $this->persistenceCacheClearer->setEnabled( false );
        $this->httpCacheClearer->switchOff();

        // Initialize legacy kernel if not already done
        if ( $this->legacyKernel instanceof Closure )
        {
            $legacyKernelClosure = $this->legacyKernel;
            $this->legacyKernel = $legacyKernelClosure();
        }

        $return = $this->legacyKernel->runCallback(
            $callback,
            false,
            false
        );

        $this->persistenceCacheClearer->setEnabled( true );
        $this->httpCacheClearer->switchOn();

        return $return;
    }
}
