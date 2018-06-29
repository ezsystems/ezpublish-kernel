<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class NotificationRendererPass implements CompilerPassInterface
{
    const TAG_NAME = 'ezpublish.notification.renderer';
    const REGISTRY_DEFINITION_ID = 'notification.renderer.registry';

    public function process(ContainerBuilder $container)
    {
        if (!$container->has(self::REGISTRY_DEFINITION_ID)) {
            return;
        }

        $registry = $container->findDefinition(self::REGISTRY_DEFINITION_ID);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['alias'])) {
                    throw new LogicException(sprintf(
                        'Tag %s needs a "alias" attribute to identify the notification type. None given.',
                        self::TAG_NAME
                    ));
                }

                $registry->addMethodCall('addRenderer', [$attribute['alias'], new Reference($id)]);
            }
        }
    }
}
