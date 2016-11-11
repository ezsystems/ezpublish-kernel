<?php

/**
 * File containing the ViewPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
            krsort($viewProvidersPerPriority);
            foreach ($viewProvidersPerPriority as $priorityViewProviders) {
                if (!isset($viewProviders[$type])) {
                    $viewProviders[$type] = [];
                }
                $viewProviders[$type] = array_merge($viewProviders[$type], $priorityViewProviders);
            }
        }

        if ($container->hasDefinition('ezpublish.view_provider.registry')) {
            $container->getDefinition('ezpublish.view_provider.registry')->addMethodCall(
                'setViewProviders',
                [$viewProviders]
            );
        }

        $flattenedViewProviders = [];
        foreach ($viewProviders as $type => $typeViewProviders) {
            foreach ($typeViewProviders as $typeViewProvider) {
                $flattenedViewProviders[] = $typeViewProvider;
            }
        }

        if ($container->hasDefinition('ezpublish.config_scope_listener')) {
            $container->getDefinition('ezpublish.config_scope_listener')->addMethodCall(
                'setViewProviders',
                [$flattenedViewProviders]
            );
        }

        // 5.4.5 BC service after location view deprecation
        if ($container->hasDefinition('ezpublish.view.custom_location_controller_checker')) {
            $container->getDefinition('ezpublish.view.custom_location_controller_checker')->addMethodCall(
                'addViewProviders',
                [$viewProviders['eZ\Publish\Core\MVC\Symfony\View\ContentView']]
            );
        }
    }
}
