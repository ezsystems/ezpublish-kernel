<?php

/**
 * File containing the ChainConfigResolverPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\SiteAccess\SiteAccessMatcherRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The SiteAccessMatcherRegistryPass will register all services tagged as "ezpublish.matcher.siteaccess" to the registry.
 */
final class SiteAccessMatcherRegistryPass implements CompilerPassInterface
{
    public const MATCHER_TAG = 'ezpublish.matcher.siteaccess';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(SiteAccessMatcherRegistry::class)) {
            return;
        }

        $matcherServiceRegistry = $container->getDefinition(SiteAccessMatcherRegistry::class);

        foreach ($container->findTaggedServiceIds(self::MATCHER_TAG) as $id => $attributes) {
            $matcherServiceRegistry->addMethodCall(
                'setMatcher',
                [
                    $id,
                    new Reference($id),
                ]
            );
        }
    }
}
