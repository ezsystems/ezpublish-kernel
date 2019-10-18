<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\Comparison\FieldRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ComparableFieldRegistryPass implements CompilerPassInterface
{
    public const FIELD_TYPE_COMPARABLE_SERVICE_TAG = 'ezplatform.field_type.comparable';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(FieldRegistry::class)) {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(FieldRegistry::class);
        $comparableFieldTypeTags = $container->findTaggedServiceIds(self::FIELD_TYPE_COMPARABLE_SERVICE_TAG);

        foreach ($comparableFieldTypeTags as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $fieldRegistryDefinition->addMethodCall(
                    'registerType',
                    [
                        $attribute['alias'],
                        new Reference($id),
                    ]
                );
            }
        }
    }
}
