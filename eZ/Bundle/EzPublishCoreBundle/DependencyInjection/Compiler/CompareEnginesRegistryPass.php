<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\Compare\CompareEngineRegistry;
use eZ\Publish\Core\Compare\FieldRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CompareEnginesRegistryPass implements CompilerPassInterface
{
    const COMPARE_ENGINE_SERVICE_TAG = 'ezplatform.compare.engine';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(CompareEngineRegistry::class)) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(FieldRegistry::class);
        $comparableFieldTypeTags = $container->findTaggedServiceIds(self::COMPARE_ENGINE_SERVICE_TAG);

        foreach ($comparableFieldTypeTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $fieldRegistryDefinition->addMethodCall(
                    'registerEngine',
                    [
                        $attribute['supported_type'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
