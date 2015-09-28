<?php

/**
 * File containing the ViewPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers services tagged as ezpublish.view_provider into the view_provider registry.
 */
class ViewProvidersPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $rawViewProviders = [];
        foreach ($container->findTaggedServiceIds('ezpublish.view_provider') as $serviceId => $tags) {
            foreach ($tags as $attributes) {
                // Priority range is between -255 (the lowest) and 255 (the highest)
                $priority = isset($attributes['priority']) ? max(min((int)$attributes['priority'], 255), -255) : 0;

                if (!isset($attributes['type'])) {
                    throw new LogicException("Missing mandatory attribute 'type' for ezpublish.view_provider tag");
                }
                $type = $attributes['type'];

                $rawViewProviders[$type][$priority][] = new Reference($serviceId);
            }
        }

        $viewProviders = [];
        foreach ($rawViewProviders as $type => $viewProvidersPerPriority) {
            ksort($viewProvidersPerPriority);
            foreach ($viewProvidersPerPriority as $priorityViewProviders) {
                if (!isset($viewProviders[$type])) {
                    $viewProviders[$type] = [];
                }
                $viewProviders[$type] = array_merge($viewProviders[$type], $priorityViewProviders);
            }
        }

        if ($container->hasDefinition('ezpublish.view.type_provider_registry')) {
            $container->getDefinition('ezpublish.view.type_provider_registry')->addMethodCall(
                'addViewProviders',
                [$viewProviders]
            );
        }

        // @todo remove...
        if ($container->hasDefinition('ezpublish.view.location_view_rule_thingie')) {
            $container->getDefinition('ezpublish.view.location_view_rule_thingie')->addMethodCall(
                'addViewProviders',
                // ...obviously
                [$viewProviders['eZ\Publish\API\Repository\Values\Content\Location']]
            );
        }
    }
}
