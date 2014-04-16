<?php
/**
 * File containing ServiceContainer class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

use eZ\Publish\API\Container;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RuntimeException;

/**
 * Container implementation wrapping Symfony container component.
 * Provides cache generation.
 */
class ServiceContainer implements Container
{
    /**
     * Holds class name for generated container cache
     *
     * @var string
     */
    protected $containerClassName = "EzPublishPublicAPIServiceContainer";

    /**
     * Holds inner Symfony container instance
     *
     * @var \Symfony\Component\DependencyInjection\Container|\Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $innerContainer;

    /**
     * Holds installation directory path
     *
     * @var string
     */
    protected $installDir;

    /**
     * Holds settings directory path
     *
     * @var string
     */
    protected $settingsDir;

    /**
     * Holds cache directory path
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Holds debug flag for cache service
     *
     * @var bool
     */
    protected $debug;

    /**
     * Holds flag whether cache should be bypassed
     *
     * @var bool
     */
    protected $bypassCache;

    /**
     * @param string $installDir Installation directory, required by default 'containerBuilder.php' file
     * @param string $settingsDir Settings directory, will be checked for 'containerBuilder.php' if
     *                            $containerBuilder is not provided
     * @param string $cacheDir Directory where PHP container cache will be stored
     * @param bool $bypassCache Default false should be used for production, if true completely bypasses
     *                          the cache, using compiled ContainerBuilder as container
     * @param bool $debug Default false should be used for production, if true resources will be checked
     *                    for cache to be regenerated if necessary
     * @param null|\Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder Optional, if not given
     *        ContainerBuilder from 'containerBuilder.php' file in settings directory will be loaded
     */
    public function __construct(
        $installDir,
        $settingsDir,
        $cacheDir,
        $bypassCache = false,
        $debug = false,
        ContainerBuilder $containerBuilder = null
    )
    {
        $this->installDir = $installDir;
        $this->settingsDir = $settingsDir;
        $this->cacheDir = $cacheDir;
        $this->bypassCache = $bypassCache;
        $this->debug = $debug;
        $this->innerContainer = $containerBuilder;

        $this->initializeContainer();
    }

    /**
     * Get Repository object
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return $this->innerContainer->get( "ezpublish.api.repository" );
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getInnerContainer()
    {
        return $this->innerContainer;
    }

    /**
     * Convenience method to inner container
     *
     * @param string $id
     *
     * @return object
     */
    public function get( $id )
    {
        return $this->innerContainer->get( $id );
    }

    /**
     * Convenience method to inner container
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        return $this->innerContainer->getParameter( $name );
    }

    /**
     * Initializes inner container
     *
     * @throws \RuntimeException
     */
    protected function initializeContainer()
    {
        // First check if cache should be bypassed
        if ( $this->bypassCache )
        {
            if ( !isset( $this->innerContainer ) )
            {
                $this->innerContainer = $this->requireContainerBuilder();
            }
            $this->innerContainer->compile();
            return;
        }

        // Prepare cache directory
        $this->prepareDirectory( $this->cacheDir, "cache" );

        // Instantiate cache
        $cache = new ConfigCache(
            $this->cacheDir . "/container/" . $this->containerClassName . ".php",
            $this->debug
        );

        // Check if cache needs to be regenerated, depends on debug being set to true
        if ( !$cache->isFresh() )
        {
            if ( !isset( $this->innerContainer ) )
            {
                $this->innerContainer = $this->requireContainerBuilder();
            }
            $this->innerContainer->compile();
            $this->dumpContainer( $cache );
        }

        // Include container cache
        require_once $cache;

        // Instantiate container
        $this->innerContainer = new $this->containerClassName;
    }

    /**
     * Returns ContainerBuilder by including the default file 'containerBuilder.php' from settings directory
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function requireContainerBuilder()
    {
        $containerBuilderFilePath = $this->settingsDir . "/containerBuilder.php";
        if ( !is_readable( $containerBuilderFilePath ) )
        {
            throw new RuntimeException(
                sprintf(
                    "Unable to read file %s\n",
                    $containerBuilderFilePath
                )
            );
        }

        // 'containerBuilder.php' file expects $installDir variable to be set by caller
        $installDir = $this->installDir;
        return require_once $containerBuilderFilePath;
    }

    /**
     * Dumps the service container to PHP code in the cache
     *
     * @param \Symfony\Component\Config\ConfigCache $cache
     */
    protected function dumpContainer( ConfigCache $cache )
    {
        $dumper = new PhpDumper( $this->innerContainer );

        if ( class_exists( 'ProxyManager\Configuration' ) )
        {
            $dumper->setProxyDumper( new ProxyDumper() );
        }

        $content = $dumper->dump(
            array(
                'class' => $this->containerClassName,
                'base_class' => "Container"
            )
        );

        $cache->write( $content, $this->innerContainer->getResources() );
    }

    /**
     * Checks for existence of given $directory and tries to create it if found missing
     *
     * @throws \RuntimeException
     *
     * @param string $directory Path to the directory
     * @param string $name Used for exception message
     */
    protected function prepareDirectory( $directory, $name )
    {
        if ( !is_dir( $directory ) )
        {
            if ( false === @mkdir( $directory, 0777, true ) )
            {
                throw new RuntimeException(
                    sprintf(
                        "Unable to create the %s directory (%s)\n",
                        $name,
                        $directory
                    )
                );
            }
        }
        else if ( !is_writable( $directory ) )
        {
            throw new RuntimeException(
                sprintf(
                    "Unable to write in the %s directory (%s)\n",
                    $name,
                    $directory
                )
            );
        }
    }
}
