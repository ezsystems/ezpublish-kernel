<?php

/**
 * File containing the ImaginePass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Imagine\Filter\FilterConfiguration;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImaginePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('liip_imagine.filter.configuration')) {
            return;
        }

        $filterConfigDef = $container->findDefinition('liip_imagine.filter.configuration');
        $filterConfigDef->setClass(FilterConfiguration::class);
        $filterConfigDef->addMethodCall('setConfigResolver', [new Reference('ezpublish.config.resolver')]);

        if ($container->hasAlias('liip_imagine')) {
            $imagineAlias = (string)$container->getAlias('liip_imagine');
            $driver = substr($imagineAlias, strrpos($imagineAlias, '.') + 1);

            $this->processReduceNoiseFilter($container, $driver);
            $this->processSwirlFilter($container, $driver);
        }
    }

    private function processReduceNoiseFilter(ContainerBuilder $container, $driver)
    {
        if ($driver !== 'imagick' && $driver !== 'gmagick') {
            return;
        }

        $container->setAlias(
            'ezpublish.image_alias.imagine.filter.reduce_noise',
            new Alias("ezpublish.image_alias.imagine.filter.reduce_noise.$driver")
        );
    }

    private function processSwirlFilter(ContainerBuilder $container, $driver)
    {
        if ($driver !== 'imagick' && $driver !== 'gmagick') {
            return;
        }

        $container->setAlias(
            'ezpublish.image_alias.imagine.filter.swirl',
            new Alias("ezpublish.image_alias.imagine.filter.swirl.$driver")
        );
    }
}
