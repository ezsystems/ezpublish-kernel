<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds all available export methods to registry.
 */
class NotificationRendererPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('notification.renderer.registry')) {
            return;
        }

        $registry = $container->findDefinition('notification.renderer.registry');

        foreach ($container->findTaggedServiceIds('ezstudio.notification.renderer') as $id => $tags) {
            foreach ($tags as $tag) {
                $registry->addMethodCall('addRenderer', [$tag['alias'], new Reference($id)]);
            }
        }
    }
}
