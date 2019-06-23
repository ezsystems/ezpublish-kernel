<?php

/**
 * File containing the RichTextEzxmlInputConverterPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass for the RichText EZXML input Aggregate converter tags.
 *
 * @see \eZ\Publish\Core\FieldType\RichText\Converter\Aggregate
 */
class RichTextEzxmlInputConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.fieldType.ezrichtext.converter.input.ezxml')) {
            return;
        }

        $ezxmlInputConverterDefinition = $container->getDefinition('ezpublish.fieldType.ezrichtext.converter.input.ezxml');
        $taggedServiceIds = $container->findTaggedServiceIds('ezpublish.ezrichtext.converter.input.ezxml');

        $convertersByPriority = [];
        foreach ($taggedServiceIds as $id => $tags) {
            foreach ($tags as $tag) {
                $priority = isset($tag['priority']) ? (int)$tag['priority'] : 0;
                $convertersByPriority[$priority][] = new Reference($id);
            }
        }

        if (count($convertersByPriority) > 0) {
            $ezxmlInputConverterDefinition->setArguments(
                [
                    $this->sortConverters($convertersByPriority),
                ]
            );
        }
    }

    /**
     * Transforms a two-dimensional array of converters, indexed by priority,
     * into a flat array of Reference objects.
     *
     * @param array $convertersByPriority
     *
     * @return \Symfony\Component\DependencyInjection\Reference[]
     */
    protected function sortConverters(array $convertersByPriority)
    {
        ksort($convertersByPriority);

        return array_merge(...$convertersByPriority);
    }
}
