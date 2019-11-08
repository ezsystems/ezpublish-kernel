<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\ProxyFactory;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory as BaseLazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use RuntimeException;

/**
 * @internal
 */
final class LazyLoadingValueHolderFactory extends BaseLazyLoadingValueHolderFactory
{
    /**
     * Use LazyLoadingValueHolderFactory::create method instead.
     */
    private function __construct(Configuration $options)
    {
        parent::__construct($options);
    }

    public function registerAutoloader(): void
    {
        spl_autoload_register($this->configuration->getProxyAutoloader());
    }

    public function warmUp(iterable $classes): void
    {
        foreach ($classes as $class) {
            $this->createProxy($class, function () {});
        }
    }

    public static function create(string $proxyCacheDir): self
    {
        $config = new Configuration();

        // Proxy cache directory needs to be created before
        if (!is_dir($proxyCacheDir)) {
            if (false === @mkdir($proxyCacheDir, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Unable to create the Repository Proxy directory "%s".',
                    $proxyCacheDir
                ));
            }
        } elseif (!is_writable($proxyCacheDir)) {
            throw new RuntimeException(sprintf(
                'The Repository Proxy directory "%s" is not writeable for the current system user.',
                $proxyCacheDir
            ));
        }

        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($proxyCacheDir)));
        $config->setProxiesTargetDir($proxyCacheDir);

        return new self($config);
    }
}
