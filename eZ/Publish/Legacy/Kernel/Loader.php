<?php
/**
 * File containing the legacy kernel Loader class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy\Kernel;

use eZ\Publish\Legacy\Kernel as LegacyKernel,
    eZ\Publish\MVC\SiteAccess,
    \ezpKernelHandler,
    \ezpKernelWeb,
    \eZSiteAccess,
    \eZURI,
    Symfony\Component\HttpFoundation\Request;

/**
 * Legacy kernel loader
 */
class Loader
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
     * @param \Symfony\Component\HttpFoundation\Request $request Current web request
     * @return \Closure
     */
    public function buildLegacyKernelHandlerWeb( Request $request )
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;
        return function () use ( $legacyRootDir, $webrootDir, $request )
        {
            static $webHandler;
            if ( !$webHandler instanceof ezpKernelWeb )
            {
                chdir( $legacyRootDir );
                $settings = array();
                if ( $request->attributes->has( 'legacySiteaccess' ) )
                    $settings['siteaccess'] = $request->attributes->get( 'legacySiteaccess' );

                $webHandler = new ezpKernelWeb( $settings );
                eZURI::instance()->setURIString(
                    $request->attributes->get(
                        'semanticPathinfo',
                        $request->getPathinfo()
                    )
                );
                chdir( $webrootDir );
            }

            return $webHandler;
        };
    }

    public function buildLegacyKernelHandlerCLI( array $settings = array() )
    {
        chdir( $this->legacyRootDir );
        $cliHandler = new CLIHandler( $settings );
        chdir( $this->webrootDir );

        return $cliHandler;
    }
}
