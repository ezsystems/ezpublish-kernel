<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Processes services tagged as ezpublish.query_type.
 */
class QueryTypePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish.query_type.registry')) {
            return;
        }

        $queryTypes = [];
        $taggedServiceIds = $container->findTaggedServiceIds('ezpublish.query_type');
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $queryTypeDefinition = $container->getDefinition($taggedServiceId);
            $queryTypeClass = $queryTypeDefinition->getClass();

            for ($i = 0, $count = count($tags); $i < $count; ++$i) {
                // TODO: Check for duplicates
                $queryTypes[$queryTypeClass::getName()] = new Reference($taggedServiceId);
            }
        }

        $aggregatorDefinition = $container->getDefinition('ezpublish.query_type.registry');
        $aggregatorDefinition->addMethodCall('addQueryTypes', [$queryTypes]);
    }
}
