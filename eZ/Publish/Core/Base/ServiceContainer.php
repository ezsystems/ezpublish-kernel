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
 *
 */
class ServiceContainer implements Container
{
    protected $containerClassName = "EzPublishPublicAPIServiceContainer";
    protected $containerBaseClassName = "Container";

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $innerContainer;
    protected $cacheDir;
    protected $settingsDir;
    protected $installDir;
    protected $debug;

    public function __construct(
        $installDir,
        $settingsDir,
        $cacheDir,
        $debug = false,
        ContainerBuilder $containerBuilder = null
    )
    {
        $this->installDir = $installDir;
        $this->settingsDir = $settingsDir;
        $this->cacheDir = $cacheDir;
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
     * @param $id
     *
     * @return object
     */
    public function get( $id )
    {
        return $this->innerContainer->get( $id );
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        return $this->innerContainer->getParameter( $name );
    }

    protected function initializeContainer()
    {
        $this->prepareDirectory( $this->cacheDir, "cache" );

        $cache = new ConfigCache(
            $this->cacheDir . "/" . $this->containerClassName . ".php",
            $this->debug
        );

        if ( $this->debug || !$cache->isFresh() )
        {
            if ( !isset( $this->innerContainer ) )
            {
                $this->innerContainer = $this->requireContainerBuilder();
            }
            else
            {
                $this->innerContainer->compile();
                return;
            }
            $this->innerContainer->compile();
            $this->dumpContainer( $cache );
        }

        require_once $cache;

        $this->innerContainer = new $this->containerClassName;
    }

    protected function requireContainerBuilder()
    {
        $installDir = $this->installDir;
        return require_once $this->settingsDir . "/container_builder.php";
    }

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
                'base_class' => $this->containerBaseClassName
            )
        );

        $cache->write( $content, $this->innerContainer->getResources() );
    }

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
