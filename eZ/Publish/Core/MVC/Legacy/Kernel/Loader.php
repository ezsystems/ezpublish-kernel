<?php
/**
 * File containing the legacy kernel Loader class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Kernel;

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use ezpKernelHandler;
use eZURI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Legacy kernel loader
 */
class Loader
{
    /**
     * @var string Absolute path to the legacy root directory (eZPublish 4 install dir)
     */
    protected $legacyRootDir;

    /**
     * @var string Absolute path to the new webroot directory (web/)
     */
    protected $webrootDir;

    /**
     * @var \Psr\Log\LoggerInterface
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
     *
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
     * @param string $webHandlerClass The legacy kernel handler class to use
     * @param array $defaultLegacyOptions Hash of options to pass to the legacy kernel handler
     *
     * @throws \InvalidArgumentException
     *
     * @return \Closure|void
     */
    public function buildLegacyKernelHandlerWeb( ContainerInterface $container, $webHandlerClass, array $defaultLegacyOptions = array() )
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;

        return function () use ( $legacyRootDir, $webrootDir, $container, $defaultLegacyOptions, $webHandlerClass )
        {
            static $webHandler;
            if ( !$webHandler instanceof ezpKernelHandler )
            {
                chdir( $legacyRootDir );

                $legacyParameters = new ParameterBag( $defaultLegacyOptions );
                $legacyParameters->set( 'service-container', $container );
                $request = $container->get( 'request' );
                $eventDispatcher = $container->get( 'event_dispatcher' );

                // PRE_BUILD_LEGACY_KERNEL for non request related stuff (potentially available in CLI context as well
                $eventDispatcher->dispatch( LegacyEvents::PRE_BUILD_LEGACY_KERNEL, new PreBuildKernelEvent( $legacyParameters ) );

                // Pure web stuff
                $buildEventWeb = new PreBuildKernelWebHandlerEvent(
                    $legacyParameters, $request
                );
                $eventDispatcher->dispatch(
                    LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB, $buildEventWeb
                );

                $interfaces = class_implements( $webHandlerClass );
                if ( !isset( $interfaces['ezpKernelHandler'] ) )
                    throw new \InvalidArgumentException( 'A legacy kernel handler must be an instance of ezpKernelHandler.' );

                $webHandler = new $webHandlerClass( $legacyParameters->all() );
                $uri = eZURI::instance();
                $uri->setURIString(
                    $request->attributes->get(
                        'semanticPathinfo',
                        $request->getPathinfo()
                    ) . $request->attributes->get( 'viewParametersString' )
                );
                chdir( $webrootDir );
            }

            return $webHandler;
        };
    }

    /**
     * Builds legacy kernel handler CLI
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @return CLIHandler
     */
    public function buildLegacyKernelHandlerCLI( ContainerInterface $container )
    {
        $legacyRootDir = $this->legacyRootDir;
        $webrootDir = $this->webrootDir;

        return function () use ( $legacyRootDir, $webrootDir, $container )
        {
            static $cliHandler;
            if ( !$cliHandler instanceof ezpKernelHandler )
            {
                chdir( $legacyRootDir );

                $legacyParameters = new ParameterBag( $container->getParameter( 'ezpublish_legacy.kernel_handler.cli.options' ) );
                $eventDispatcher = $container->get( 'event_dispatcher' );
                $eventDispatcher->dispatch( LegacyEvents::PRE_BUILD_LEGACY_KERNEL, new PreBuildKernelEvent( $legacyParameters ) );

                $cliHandler = new CLIHandler( $legacyParameters->all(), $container->get( 'ezpublish.siteaccess' ), $container );
                chdir( $webrootDir );
            }

            return $cliHandler;
        };
    }

    /**
     * Builds the legacy kernel handler for the tree menu in admin interface.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @return \Closure A closure returning an \ezpKernelTreeMenu instance.
     */
    public function buildLegacyKernelHandlerTreeMenu( ContainerInterface $container )
    {
        return $this->buildLegacyKernelHandlerWeb(
            $container,
            $container->getParameter( 'ezpublish_legacy.kernel_handler.treemenu.class' ),
            array(
                'use-cache-headers'    => false,
                'use-exceptions'       => true
            )
        );
    }
}
