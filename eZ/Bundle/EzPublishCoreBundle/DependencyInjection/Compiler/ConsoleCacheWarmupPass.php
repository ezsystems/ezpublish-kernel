<?php

/**
 * File containing the ConsoleCacheWarmupPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Cache related compiler pass.
 *
 * Ensures class cache warmup is disabled in console mode.
 */
class ConsoleCacheWarmupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // This pass is CLI only as CLI class cache warmup conflicts with web access, see EZP-29034
        if (PHP_SAPI !== 'cli' ||
            !$container->hasDefinition('kernel.class_cache.cache_warmer')) {
            return;
        }

        $warmers = [];
        foreach ($container->findTaggedServiceIds('kernel.cache_warmer') as $id => $attributes) {
            if ($id === 'kernel.class_cache.cache_warmer') {
                continue;
            }

            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $warmers[$priority][] = new Reference($id);
        }

        if (empty($warmers)) {
            return;
        }

        // sort by priority and flatten
        krsort($warmers);
        $warmers = \call_user_func_array('array_merge', $warmers);

        $container->getDefinition('cache_warmer')->replaceArgument(0, $warmers);
    }
}
