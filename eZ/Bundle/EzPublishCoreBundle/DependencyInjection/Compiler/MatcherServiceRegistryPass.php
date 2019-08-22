<?php

/**
 * File containing the ChainConfigResolverPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Matcher\MatcherServiceRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The MatcherServiceRegistryPass will register all services tagged as "ezpublish.matcher.content_based" to the registry.
 */
class MatcherServiceRegistryPass implements CompilerPassInterface
{
    public const MATCHER_TAG = 'ezpublish.matcher.content_based';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(MatcherServiceRegistry::class)) {
            return;
        }

        $matcherServiceRegistry = $container->getDefinition(MatcherServiceRegistry::class);

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
