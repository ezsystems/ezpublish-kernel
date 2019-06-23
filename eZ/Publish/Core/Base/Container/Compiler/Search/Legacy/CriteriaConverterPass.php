<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\Compiler\Search\Legacy;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass will register Legacy Search Engine criterion handlers.
 */
class CriteriaConverterPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (
            !$container->hasDefinition('ezpublish.search.legacy.gateway.criteria_converter.content') &&
            !$container->hasDefinition('ezpublish.search.legacy.gateway.criteria_converter.location') &&
            !$container->hasDefinition('ezpublish.spi.persistence.legacy.url.criterion_converter')
        ) {
            return;
        }

        if ($container->hasDefinition('ezpublish.search.legacy.gateway.criteria_converter.content')) {
            $criteriaConverterContent = $container->getDefinition('ezpublish.search.legacy.gateway.criteria_converter.content');

            $contentHandlers = $container->findTaggedServiceIds('ezpublish.search.legacy.gateway.criterion_handler.content');

            $this->addHandlers($criteriaConverterContent, $contentHandlers);
        }

        if ($container->hasDefinition('ezpublish.search.legacy.gateway.criteria_converter.location')) {
            $criteriaConverterLocation = $container->getDefinition('ezpublish.search.legacy.gateway.criteria_converter.location');

            $locationHandlers = $container->findTaggedServiceIds('ezpublish.search.legacy.gateway.criterion_handler.location');

            $this->addHandlers($criteriaConverterLocation, $locationHandlers);
        }

        if ($container->hasDefinition('ezpublish.spi.persistence.legacy.url.criterion_converter')) {
            $urlCriteriaConverter = $container->getDefinition('ezpublish.spi.persistence.legacy.url.criterion_converter');
            $urlCriteriaHandlers = $container->findTaggedServiceIds('ezpublish.persistence.legacy.url.criterion_handler');

            $this->addHandlers($urlCriteriaConverter, $urlCriteriaHandlers);
        }
    }

    protected function addHandlers(Definition $definition, $handlers)
    {
        foreach ($handlers as $id => $attributes) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}
