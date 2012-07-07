<?php
/**
 * File containing the KernelLoader class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy;

use eZ\Publish\Legacy\Kernel as LegacyKernel,
    \ezpKernelHandler,
    \ezpKernelWeb;

/**
 * Legacy kernel loader
 */
class KernelLoader
{
    /**
     * @var string $legacyRootDir Absolute path to the legacy root directory (eZPublish 4 install dir)
     */
    protected $legacyRootDir;

    /**
     * @var string Absolute path to the new webroot directory (web/)
     */
    protected $webrootDir;

    public function __construct( $legacyRootDir, $webrootDir )
    {
        $this->legacyRootDir = $legacyRootDir;
        $this->webrootDir = $webrootDir;
    }

    /**
     * Builds up the legacy kernel and encapsulates it inside a closure, allowing lazy loading.
     *
     * @param \ezpKernelHandler|\Closure A kernel handler instance or a closure returning a kernel handler instance
     * @return \Closure
     */
    public function buildLegacyKernel( $legacyKernelHandler )
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;
        return function () use ( $legacyKernelHandler, $legacyRootDir, $webrootDir )
        {
            static $legacyKernel;
            if ( !$legacyKernel instanceof LegacyKernel )
            {
                if ( $legacyKernelHandler instanceof \Closure )
                    $legacyKernelHandler = $legacyKernelHandler();
                $legacyKernel = new LegacyKernel( $legacyKernelHandler, $legacyRootDir, $webrootDir );
            }

            return $legacyKernel;
        };
    }

    /**
     * Builds up the legacy kernel web handler and encapsulates it inside a closure, allowing lazy loading.
     *
     * @return \Closure
     */
    public function buildLegacyKernelHandlerWeb()
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;
        return function () use ( $legacyRootDir, $webrootDir )
        {
            static $webHandler;
            if ( !$webHandler instanceof ezpKernelWeb )
            {
                chdir( $legacyRootDir );
                $webHandler = new ezpKernelWeb();
                chdir( $webrootDir );
            }

            return $webHandler;
        };
    }
}
