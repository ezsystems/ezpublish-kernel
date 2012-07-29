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
    eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PreBuildKernelWebHandlerEvent,
    \ezpKernelHandler,
    \ezpKernelWeb,
    \eZSiteAccess,
    \eZURI,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\Exception\InactiveScopeException,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\HttpFoundation\ParameterBag;

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

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    protected $logger;

    public function __construct( $legacyRootDir, $webrootDir, LoggerInterface $logger = null )
    {
        $this->legacyRootDir = $legacyRootDir;
        $this->webrootDir = $webrootDir;
        $this->logger = $logger;
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
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return \Closure|void
     */
    public function buildLegacyKernelHandlerWeb( ContainerInterface $container, array $defaultLegacyOptions = array() )
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;
        try
        {
            // Getting the request through the container since this service is in the "request" scope and we are not in this scope yet.
            // Moreover, while clearing/warming up caches with app/console we might get an InactiveScopeException
            // since the "request" scope is only active via web.
            $request = $container->get( 'request' );
        }
        catch ( InactiveScopeException $e )
        {
            // Not in web mode. We have nothing to do here.
            if ( isset( $this->logger ) )
                $this->logger->info( 'Trying to get the request in non-web context (warming up caches?), aborting', array( __METHOD__ ) );

            return;
        }

        $eventDispatcher = $container->get( 'event_dispatcher' );
        $legacyParameters = new ParameterBag( $defaultLegacyOptions );

        return function () use ( $legacyRootDir, $webrootDir, $request, $eventDispatcher, $legacyParameters )
        {
            static $webHandler;
            if ( !$webHandler instanceof ezpKernelWeb )
            {
                chdir( $legacyRootDir );

                $buildEvent = new PreBuildKernelWebHandlerEvent(
                    $legacyParameters, $request
                );
                $eventDispatcher->dispatch(
                    MVCEvents::BUILD_KERNEL_WEB_HANDLER, $buildEvent
                );

                $settings = $legacyParameters->all();

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
