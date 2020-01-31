<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\ProxyFactory;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\VirtualProxyInterface;
use RuntimeException;

/**
 * @internal
 */
final class ProxyGenerator implements ProxyGeneratorInterface
{
    /** @var \ProxyManager\Factory\LazyLoadingValueHolderFactory|null */
    private $lazyLoadingValueHolderFactory;

    /** @var string */
    private $proxyCacheDir;

    public function __construct(string $proxyCacheDir)
    {
        $this->proxyCacheDir = $proxyCacheDir;
    }

    public function createProxy(
        string $className,
        Closure $initializer,
        array $proxyOptions = []
    ): VirtualProxyInterface {
        if ($this->lazyLoadingValueHolderFactory === null) {
            $this->lazyLoadingValueHolderFactory = $this->createLazyLoadingValueHolderFactory();
        }

        return $this->lazyLoadingValueHolderFactory->createProxy($className, $initializer, $proxyOptions);
    }

    public function warmUp(iterable $classes): void
    {
        foreach ($classes as $class) {
            $this->createProxy($class, function () {});
        }
    }

    private function createLazyLoadingValueHolderFactory(): LazyLoadingValueHolderFactory
    {
        $config = new Configuration();

        // Proxy cache directory needs to be created before
        if (!is_dir($this->proxyCacheDir)) {
            if (false === @mkdir($this->proxyCacheDir, 0777, true)) {
                if (!is_dir($this->proxyCacheDir)) {
                    $error = error_get_last();

                    throw new RuntimeException(sprintf(
                        'Unable to create the Repository Proxy directory "%s": %s',
                        $this->proxyCacheDir,
                        $error['message']
                    ));
                }
            }
        } elseif (!is_writable($this->proxyCacheDir)) {
            throw new RuntimeException(sprintf(
                'The Repository Proxy directory "%s" is not writeable for the current system user.',
                $this->proxyCacheDir
            ));
        }

        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($this->proxyCacheDir)));
        $config->setProxiesTargetDir($this->proxyCacheDir);

        spl_autoload_register($config->getProxyAutoloader());

        return new LazyLoadingValueHolderFactory($config);
    }
}
