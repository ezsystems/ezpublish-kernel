<?php

/**
 * File containing the XmlTextConverterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass adding pre-converters to HTML5 converter.
 * Useful for manipulating internal XML before XSLT rendering.
 */
class XmlTextConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.fieldType.ezxmltext.converter.html5')) {
            return;
        }

        $html5ConverterDef = $container->getDefinition('ezpublish.fieldType.ezxmltext.converter.html5');
        $taggedServiceIds = $container->findTaggedServiceIds('ezpublish.ezxml.converter');

        $converterIdsByPriority = array();
        foreach ($taggedServiceIds as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? (int)$tag['priority'] : 0;
                $converterIdsByPriority[$priority][] = $id;
            }
        }

        $converterIdsByPriority = $this->sortConverterIds($converterIdsByPriority);

        foreach ($converterIdsByPriority as $referenceId) {
            $html5ConverterDef->addMethodCall('addPreConverter', array(new Reference($referenceId)));
        }
    }

    /**
     * Transforms a two-dimensional array of converters, indexed by priority,
     * into a flat array of Reference objects.
     *
     * @param array $converterIdsByPriority
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function sortConverterIds(array $converterIdsByPriority)
    {
        krsort($converterIdsByPriority, SORT_NUMERIC);

        return call_user_func_array('array_merge', $converterIdsByPriority);
    }
}
