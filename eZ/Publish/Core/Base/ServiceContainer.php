<?php

/**
 * File containing ServiceContainer class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base;

use eZ\Publish\API\Container;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RuntimeException;

/**
 * Container implementation wrapping Symfony container component.
 * Provides cache generation.
 */
class ServiceContainer implements Container
{
    /**
     * Holds class name for generated container cache.
     *
     * @var string
     */
    protected $containerClassName = 'EzPublishPublicAPIServiceContainer';

    /**
     * Holds inner Symfony container instance.
     *
     * @var \Symfony\Component\DependencyInjection\Container|\Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $innerContainer;

    /**
     * Holds installation directory path.
     *
     * @var string
     */
    protected $installDir;

    /**
     * Holds cache directory path.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Holds debug flag for cache service.
     *
     * @var bool
     */
    protected $debug;

    /**
     * Holds flag whether cache should be bypassed.
     *
     * @var bool
     */
    protected $bypassCache;

    /**
     * @param string|ContainerInterface $container Path to the container file or container instance
     * @param string $installDir Installation directory, required by default 'containerBuilder.php' file
     * @param string $cacheDir Directory where PHP container cache will be stored
     * @param bool $debug Default false should be used for production, if true resources will be checked
     *                    and cache will be regenerated if necessary
     * @param bool $bypassCache Default false should be used for production, if true completely bypasses the cache
     */
    public function __construct($container, $installDir, $cacheDir, $debug = false, $bypassCache = false)
    {
        $this->innerContainer = $container;
        $this->installDir = $installDir;
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
        $this->bypassCache = $bypassCache;

        $this->initializeContainer();
    }

    /**
     * Get Repository object.
     *
     * Public API for
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
        return $this->innerContainer->get('ezpublish.api.repository');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getInnerContainer()
    {
        return $this->innerContainer;
    }

    /**
     * Convenience method to inner container.
     *
     * @param string $id
     *
     * @return object
     */
    public function get($id)
    {
        return $this->innerContainer->get($id);
    }

    /**
     * Convenience method to inner container.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->innerContainer->getParameter($name);
    }

    /**
     * Initializes inner container.
     *
     * @throws \RuntimeException
     */
    protected function initializeContainer()
    {
        // First check if cache should be bypassed
        if ($this->bypassCache) {
            $this->getContainer();

            return;
        }

        // Prepare cache directory
        $this->prepareDirectory($this->cacheDir, 'cache');

        // Instantiate cache
        $cache = new ConfigCache(
            $this->cacheDir . '/container/' . $this->containerClassName . '.php',
            $this->debug
        );

        // Check if cache needs to be regenerated, depends on debug being set to true
        if (!$cache->isFresh()) {
            $this->getContainer();
            $this->dumpContainer($cache);
        }

        // Include container cache
        require_once $cache;

        // Instantiate container
        $this->innerContainer = new $this->containerClassName();
    }

    /**
     * Returns ContainerBuilder by including the default file 'containerBuilder.php' from settings directory.
     *
     * @throws \RuntimeException
     */
    protected function getContainer()
    {
        if ($this->innerContainer instanceof ContainerInterface) {
            // Do nothing
        } elseif (!is_readable($this->innerContainer)) {
            throw new RuntimeException(
                sprintf(
                    "Unable to read file %s\n",
                    $this->innerContainer
                )
            );
        } else {
            // 'containerBuilder.php' file expects $installDir variable to be set by caller
            $installDir = $this->installDir;
            $this->innerContainer = require_once $this->innerContainer;
        }

        // Compile container if necessary
        if ($this->innerContainer instanceof ContainerBuilder && !$this->innerContainer->isFrozen()) {
            $this->innerContainer->compile();
        }
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param \Symfony\Component\Config\ConfigCache $cache
     */
    protected function dumpContainer(ConfigCache $cache)
    {
        $dumper = new PhpDumper($this->innerContainer);

        if (class_exists('ProxyManager\Configuration')) {
            $dumper->setProxyDumper(new ProxyDumper());
        }

        $content = $dumper->dump(
            [
                'class' => $this->containerClassName,
                'base_class' => 'Container',
            ]
        );

        $cache->write($content, $this->innerContainer->getResources());
    }

    /**
     * Checks for existence of given $directory and tries to create it if found missing.
     *
     * @throws \RuntimeException
     *
     * @param string $directory Path to the directory
     * @param string $name Used for exception message
     */
    protected function prepareDirectory($directory, $name)
    {
        if (!is_dir($directory)) {
            if (!@mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new RuntimeException(
                    sprintf(
                        "Unable to create the %s directory (%s)\n",
                        $name,
                        $directory
                    )
                );
            }
        } elseif (!is_writable($directory)) {
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
