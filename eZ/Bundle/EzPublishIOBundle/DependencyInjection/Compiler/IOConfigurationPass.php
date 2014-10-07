<?php
/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will tweak the locale_listener service.
 */
class IOConfigurationPass implements CompilerPassInterface
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        if ( !$container->hasParameter( 'ez_io.metadata_handlers' ) && !$container->hasParameter( 'ez_io.binarydata_handlers' ) )
        {
            return;
        }

        $this->processHandlers(
            $container,
            $container->getDefinition( 'ezpublish.core.io.metadata_handler.factory' ),
            $container->getParameter( 'ez_io.metadata_handlers' ),
            $container->getParameter( 'ez_io.metadata_handlers_map' ),
            'ezpublish.core.io.metadata_handler.flysystem.default'
        );

        $this->processHandlers(
            $container,
            $container->getDefinition( 'ezpublish.core.io.binarydata_handler.factory' ),
            $container->getParameter( 'ez_io.binarydata_handlers' ),
            $container->getParameter( 'ez_io.binarydata_handlers_map' ),
            'ezpublish.core.io.binarydata_handler.flysystem.default'
        );

        // Unset parameters that are no longer required ?
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $factory The factory service that should receive the list of handlers
     * @param array $handlers Handlers configuration declared via semantic config
     * @param array $handlerTypesMap Map of alias => handler service id
     * @param string $defaultHandler default handler id
     *
     * @internal param $HandlerTypesMap
     */
    protected function processHandlers( ContainerBuilder $container, Definition $factory, array $handlers, array $handlerTypesMap, $defaultHandler )
    {
        $handlersMap = array( 'default' => $defaultHandler );

        foreach ( $handlers as $type => $typeArray )
        {
            if ( !isset( $handlerTypesMap[$type] ) )
            {
                throw new InvalidConfigurationException( "Unknown handler type $type" );
            }

            $parentHandlerId = $handlerTypesMap[$type];

            foreach ( $typeArray as $name => $config )
            {
                $id = sprintf( '%s.%s', $parentHandlerId, $name );

                $definition = $container->setDefinition( $id, new DefinitionDecorator( $parentHandlerId ) );

                if ( $type == 'flysystem' )
                {
                    $adapterId = sprintf( 'oneup_flysystem.%s_adapter', $config['adapter'] );
                    if ( !$container->hasDefinition( $adapterId ) )
                    {
                        throw new InvalidConfigurationException( "Unknown flysystem adapter {$config['adapter']}" );
                    }
                    $definition->replaceArgument( 0, new Reference( $adapterId ) );
                }
                else if ( $type == 'legacy_dfs_cluster' )
                {
                    $definition->replaceArgument( 0, new Reference( $config['connection'] ) );
                }

                $handlersMap[$name] = $id;
            }
        }

        $factory->addMethodCall( 'setHandlersMap', array( $handlersMap ) );
    }
}
