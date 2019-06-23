<?php

/**
 * File containing the AsseticPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\Assetic\AssetFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tweaks Assetic services.
 */
class AsseticPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('assetic.asset_factory')) {
            return;
        }

        $assetFactoryDef = $container->findDefinition('assetic.asset_factory');
        $assetFactoryDef
            ->setClass(AssetFactory::class)
            ->addMethodCall('setConfigResolver', [new Reference('ezpublish.config.resolver')])
            ->addMethodCall(
                'setDynamicSettingParser',
                [new Reference('ezpublish.config.dynamic_setting.parser')]
            );
    }
}
