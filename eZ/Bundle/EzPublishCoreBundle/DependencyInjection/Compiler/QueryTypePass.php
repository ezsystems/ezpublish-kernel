<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use AppendIterator;
use ArrayIterator;
use Iterator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Processes services tagged as ezplatform.query_type, and registers them with ezpublish.query_type.registry.
 */
final class QueryTypePass implements CompilerPassInterface
{
    public const QUERY_TYPE_SERVICE_TAG = 'ezplatform.query_type';
    public const DEPRECATED_QUERY_TYPE_SERVICE_TAG = 'ezpublish.query_type';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('ezpublish.query_type.registry')) {
            return;
        }

        $queryTypes = [];

        $iterator = $this->getTaggedServiceIdsIterator($container);
        foreach ($iterator as $taggedServiceId => $tags) {
            $queryTypeDefinition = $container->getDefinition($taggedServiceId);
            $queryTypeClass = $container->getParameterBag()->resolveValue($queryTypeDefinition->getClass());

            for ($i = 0, $count = count($tags); $i < $count; ++$i) {
                $name = isset($tags[$i]['alias']) ? $tags[$i]['alias'] : $queryTypeClass::getName();
                $queryTypes[$name] = new Reference($taggedServiceId);
            }
        }

        $aggregatorDefinition = $container->getDefinition('ezpublish.query_type.registry');
        $aggregatorDefinition->addMethodCall('addQueryTypes', [$queryTypes]);
    }

    private function getTaggedServiceIdsIterator(ContainerBuilder $container): Iterator
    {
        $serviceIdsWithDeprecatedTags = $container->findTaggedServiceIds(
            self::DEPRECATED_QUERY_TYPE_SERVICE_TAG
        );

        foreach ($serviceIdsWithDeprecatedTags as $serviceId => $tags) {
            @trigger_error(
                sprintf(
                    'Service tag `%s` is deprecated and will be removed in eZ Platform 4.0. Tag %s with `%s` instead.',
                    self::DEPRECATED_QUERY_TYPE_SERVICE_TAG,
                    $serviceId,
                    self::QUERY_TYPE_SERVICE_TAG
                ),
                E_USER_DEPRECATED
            );
        }

        $taggedServiceIds = $container->findTaggedServiceIds(self::QUERY_TYPE_SERVICE_TAG);

        $iterator = new AppendIterator();
        $iterator->append(new ArrayIterator($serviceIdsWithDeprecatedTags));
        $iterator->append(new ArrayIterator($taggedServiceIds));

        return $iterator;
    }
}
