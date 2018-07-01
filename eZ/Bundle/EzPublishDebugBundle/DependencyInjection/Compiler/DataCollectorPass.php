<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishDebugBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish_debug.data_collector')) {
            return;
        }

        $dataCollectorDef = $container->getDefinition('ezpublish_debug.data_collector');
        foreach ($container->findTaggedServiceIds('ezpublish_data_collector') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $dataCollectorDef->addMethodCall(
                    'addCollector',
                    [
                        new Reference($id),
                        isset($attribute['panelTemplate']) ? $attribute['panelTemplate'] : null,
                        isset($attribute['toolbarTemplate']) ? $attribute['toolbarTemplate'] : null,
                    ]
                );
            }
        }
    }
}
