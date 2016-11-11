<?php

/**
 * File containing the ViewPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * The ViewPass adds DIC compiler pass related to content view.
 * This includes adding ContentViewProvider implementations.
 *
 * @see \eZ\Publish\Core\MVC\Symfony\View\Manager
 * @see \eZ\Publish\Core\MVC\Symfony\View\ContentViewProvider
 *
 * @deprecated since 6.0
 *
 * Converts the old tag (ezpublish.xxx_view_provider) to the new one (ezpublish.view_provider with type attribute)
 */
abstract class ViewManagerPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds(static::VIEW_PROVIDER_IDENTIFIER) as $id => $attributes) {
            $taggedServiceDefinition = $container->getDefinition($id);
            foreach ($attributes as $attribute) {
                // @todo log deprecated message
                $priority = isset($attribute['priority']) ? (int)$attribute['priority'] : 0;
                $taggedServiceDefinition->clearTag(static::VIEW_PROVIDER_IDENTIFIER);
                $taggedServiceDefinition->addTag(
                    'ezpublish.view_provider',
                    ['type' => static::VIEW_TYPE, 'priority' => $priority]
                );
            }
            $container->setDefinition($id, $taggedServiceDefinition);
        }
    }
}
