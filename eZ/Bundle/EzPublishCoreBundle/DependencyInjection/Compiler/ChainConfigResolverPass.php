<?php

/**
 * File containing the ChainConfigResolverPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The ChainConfigResolverPass will register all services tagged as "ezpublish.config.resolver" to the chain config resolver.
 */
class ChainConfigResolverPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ChainConfigResolver::class)) {
            return;
        }

        $chainResolver = $container->getDefinition(ChainConfigResolver::class);

        foreach ($container->findTaggedServiceIds('ezpublish.config.resolver') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? (int)$attributes[0]['priority'] : 0;
            // Priority range is between -255 (the lowest) and 255 (the highest)
            if ($priority > 255) {
                $priority = 255;
            }
            if ($priority < -255) {
                $priority = -255;
            }

            $chainResolver->addMethodCall(
                'addResolver',
                [
                    new Reference($id),
                    $priority,
                ]
            );
        }
    }
}
