<?php

/**
 * File containing the OutputVisitorPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Compiler pass for the ezpublish_rest.output.visitor tag.
 *
 * Maps an output visitor (json, xml...) to an accept-header
 *
 * @todo The tag is much more limited in scope than the name shows. Refactor. More ways to map ?
 */
class OutputVisitorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezpublish_rest.output.visitor.dispatcher')) {
            return;
        }

        $definition = $container->getDefinition('ezpublish_rest.output.visitor.dispatcher');

        $visitors = [];

        foreach ($container->findTaggedServiceIds('ezpublish_rest.output.visitor') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $priority = isset($attribute['priority']) ? $attribute['priority'] : 0;

                if (!isset($attribute['regexps'])) {
                    throw new \LogicException('ezpublish_rest.output.visitor service tag needs a "regexps" attribute to identify the Accept header. None given.');
                }

                if (is_array($attribute['regexps'])) {
                    $regexps = $attribute['regexps'];
                } elseif (is_string($attribute['regexps'])) {
                    try {
                        $regexps = $container->getParameter($attribute['regexps']);
                    } catch (InvalidArgumentException $e) {
                        throw new \LogicException("The regexps attribute of the ezpublish_rest.output.visitor service tag can be a string matching a container parameter name. No parameter {$attribute['regexps']} could be found.");
                    }
                } else {
                    throw new \LogicException('ezpublish_rest.output.visitor service tag needs a "regexps" attribute, either as an array or a string. Invalid value.');
                }

                $visitors[$priority][] = [
                    'regexps' => $regexps,
                    'reference' => new Reference($id),
                ];
            }
        }

        // sort by priority and flatten
        krsort($visitors);
        $visitors = array_merge(...$visitors);

        foreach ($visitors as $visitor) {
            foreach ($visitor['regexps'] as $regexp) {
                $definition->addMethodCall(
                    'addVisitor', [
                        $regexp,
                        $visitor['reference'],
                    ]
                );
            }
        }
    }
}
