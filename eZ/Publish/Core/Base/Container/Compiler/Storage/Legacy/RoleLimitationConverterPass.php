<?php

/**
 * File containing the RoleLimitationConverterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Storage\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register Legacy Storage role limitation converters.
 */
class RoleLimitationConverterPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.persistence.legacy.role.limitation.converter')) {
            return;
        }

        $roleLimitationConverter = $container->getDefinition('ezpublish.persistence.legacy.role.limitation.converter');

        foreach ($container->findTaggedServiceIds('ezpublish.persistence.legacy.role.limitation.handler') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $roleLimitationConverter->addMethodCall(
                    'addHandler',
                    [new Reference($id)]
                );
            }
        }
    }
}
