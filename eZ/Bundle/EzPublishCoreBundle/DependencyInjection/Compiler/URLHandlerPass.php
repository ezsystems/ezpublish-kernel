<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will register URL handlers.
 */
class URLHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.url_checker.handler_registry')) {
            return;
        }

        $definition = $container->findDefinition('ezpublish.url_checker.handler_registry');
        foreach ($container->findTaggedServiceIds('ezpublish.url_handler') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['scheme'])) {
                    throw new LogicException(sprintf(
                        '%s service tag needs a "scheme" attribute to identify which scheme is supported by handler. None given.',
                        'ezpublish.url_handler'
                    ));
                }

                $definition->addMethodCall('addHandler', [
                    $attribute['scheme'],
                    new Reference($id),
                ]);
            }
        }
    }
}
