<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base\Container\Compiler\Search\Solr;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use LogicException;

/**
 * This compiler pass will register Solr Endpoints.
 */
class EndpointRegistryPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if (
            !$container->hasDefinition(
                "ezpublish.search.solr.content.gateway.endpoint_registry"
            )
        )
        {
            return;
        }

        $fieldRegistryDefinition = $container->getDefinition(
            "ezpublish.search.solr.content.gateway.endpoint_registry"
        );

        $endpoints = $container->findTaggedServiceIds( "ezpublish.search.solr.endpoint" );

        foreach ( $endpoints as $id => $attributes )
        {
            foreach ( $attributes as $attribute )
            {
                if ( !isset( $attribute["alias"] ) )
                {
                    throw new LogicException(
                        "'ezpublish.search.solr.endpoint' service tag needs an 'alias' attribute " .
                        "to identify the Endpoint. None given."
                    );
                }

                $fieldRegistryDefinition->addMethodCall(
                    "registerEndpoint",
                    array(
                        $attribute["alias"],
                        new Reference( $id ),
                    )
                );
            }
        }
    }
}
