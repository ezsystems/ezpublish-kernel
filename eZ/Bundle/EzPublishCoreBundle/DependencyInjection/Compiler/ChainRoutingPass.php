<?php

/**
 * File containing the ChainRoutingPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The ChainRoutingPass will register all services tagged as "router" to the chain router.
 */
class ChainRoutingPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.chain_router')) {
            return;
        }

        $chainRouter = $container->getDefinition('ezpublish.chain_router');

        // Enforce default router to be part of the routing chain
        // The default router will be given the highest priority so that it will be used by default
        if ($container->hasDefinition('router.default')) {
            $defaultRouter = $container->getDefinition('router.default');
            $defaultRouter->addMethodCall('setSiteAccess', array(new Reference('ezpublish.siteaccess')));
            $defaultRouter->addMethodCall('setConfigResolver', array(new Reference('ezpublish.config.resolver')));
            $defaultRouter->addMethodCall(
                'setNonSiteAccessAwareRoutes',
                array('%ezpublish.default_router.non_siteaccess_aware_routes%')
            );
            $defaultRouter->addMethodCall(
                'setSiteAccessRouter',
                array(new Reference('ezpublish.siteaccess_router'))
            );
            if (!$defaultRouter->hasTag('router')) {
                $defaultRouter->addTag(
                    'router',
                    array('priority' => 255)
                );
            }
        }

        foreach ($container->findTaggedServiceIds('router') as $id => $attributes) {
            $priority = isset($attributes[0]['priority']) ? (int)$attributes[0]['priority'] : 0;
            // Priority range is between -255 (the lowest) and 255 (the highest)
            if ($priority > 255) {
                $priority = 255;
            }
            if ($priority < -255) {
                $priority = -255;
            }

            $chainRouter->addMethodCall(
                'add',
                array(
                    new Reference($id),
                    $priority,
                )
            );
        }
    }
}
