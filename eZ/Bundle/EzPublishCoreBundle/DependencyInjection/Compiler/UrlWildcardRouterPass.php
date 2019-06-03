<?php

/**
 * File containing the ChainRoutingPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The UrlWildcardRouterPass registers UrlWildcardRouter if enabled in the configuration.
 * It needs to be executed before \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\ChainRoutingPass.
 */
class UrlWildcardRouterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (
            !$container->hasParameter('ezpublish.url_wildcards.enabled') ||
            !$container->hasParameter('ezpublish.urlwildcard_router.class')
        ) {
            return;
        }

        if ($container->getParameter('ezpublish.url_wildcards.enabled') === true) {
            $definition = new Definition();
            $definition->setClass($container->getParameter('ezpublish.urlwildcard_router.class'));
            $definition->setPublic(true);
            $definition->setArguments(
                [
                    new Reference('ezpublish.api.service.url_wildcard'),
                    new Reference('ezpublish.urlalias_generator'),
                    new Reference('router.request_context', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                    new Reference('logger'),
                ]
            );
            $definition->addTag('router', ['priority' => 210]);
            $container->setDefinition('ezpublish.urlwildcard_router', $definition);
        }
    }
}
