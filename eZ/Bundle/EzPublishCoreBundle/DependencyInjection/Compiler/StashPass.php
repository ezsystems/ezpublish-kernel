<?php

/**
 * File containing the StashPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisIgbinary;
use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisIgbinaryLzf;
use eZ\Bundle\EzPublishCoreBundle\Cache\Driver\Redis\RedisSerializeLzf;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass overrides default Stash's Redis driver with one of stored in eZ/Bundle/EzPublishCoreBundle/Cache/Driver.
 */
class StashPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('stash.driver')) {
            return;
        }

        $igbinary = $container->hasParameter('ezpublish.stash_cache.igbinary') ? $container->getParameter('ezpublish.stash_cache.igbinary') : false;
        $lzf = $container->hasParameter('ezpublish.stash_cache.lzf') ? $container->getParameter('ezpublish.stash_cache.lzf') : false;

        $config = $container->getExtensionConfig('stash');
        $config = reset($config);
        foreach ($config['caches'] as $name => $configuration) {
            if (in_array('Redis', $configuration['drivers'], true)) {
                $this->configureRedis($container, $igbinary, $lzf);
            }
        }

        $stashDriverDef = $container->findDefinition('stash.driver');
        $stashDriverDef->setFactory(
            [
                '%ezpublish.stash_cache.driver_factory.class%',
                'registerAndCreateDriver',
            ]
        );
        $stashDriverDef->setArguments(
            [
                '%ezpublish.stash_cache.redis_driver.name%',
                '%ezpublish.stash_cache.redis_driver.class%',
            ]
        );
        $stashDriverDef->setSynthetic(true);
        $stashDriverDef->setAbstract(true);
    }

    private function configureRedis(ContainerBuilder $container, $igbinary, $lzf)
    {
        if ($igbinary && $lzf) {
            $container->setParameter('ezpublish.stash_cache.redis_driver.class', RedisIgbinaryLzf::class);
        } elseif ($igbinary && !$lzf) {
            $container->setParameter('ezpublish.stash_cache.redis_driver.class', RedisIgbinary::class);
        } elseif (!$igbinary && $lzf) {
            $container->setParameter('ezpublish.stash_cache.redis_driver.class', RedisSerializeLzf::class);
        }
    }
}
