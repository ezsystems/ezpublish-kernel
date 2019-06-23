<?php

/**
 * File containing the LocalePass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will tweak the locale_listener service.
 */
class LocalePass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('locale_listener')) {
            return;
        }

        $localeListenerDef = $container->getDefinition('locale_listener');
        // Injecting the service container for lazy loading purpose, since all event listeners are instantiated before events are triggered
        $localeListenerDef->addMethodCall('setConfigResolver', [new Reference('ezpublish.config.resolver')]);
        $localeListenerDef->addMethodCall('setLocaleConverter', [new Reference('ezpublish.locale.converter')]);
    }
}
