<?php
/**
 * File containing the IOConfigurationPass class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This compiler pass will create the metadata and binarydata IO handlers depending on container configuration.
 *
 * @todo Refactor into two passes, since they're very very close.
 */
class IOConfigurationPass implements CompilerPassInterface
{
    /** @var ConfigurationFactory[] */
    private $metadataHandlerFactories;

    /** @var ConfigurationFactory[] */
    private $binarydataHandlerFactories;

    public function __construct(
        array $metadataHandlerFactories = array(),
        array $binarydataHandlerFactories = array()
    )
    {
        $this->metadataHandlerFactories = $metadataHandlerFactories;
        $this->binarydataHandlerFactories = $binarydataHandlerFactories;
    }
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process( ContainerBuilder $container )
    {
        $ioMetadataHandlers = $container->hasParameter( 'ez_io.metadata_handlers' ) ?
            $container->getParameter( 'ez_io.metadata_handlers' ) :
            array();
        $this->processHandlers(
            $container,
            $container->getDefinition( 'ezpublish.core.io.metadata_handler.factory' ),
            $ioMetadataHandlers,
            $container->getParameter( 'ez_io.available_metadata_handler_types' ),
            'ezpublish.core.io.metadata_handler.flysystem.default'
        );

        $ioBinarydataHandlers = $container->hasParameter( 'ez_io.binarydata_handlers' ) ?
            $container->getParameter( 'ez_io.binarydata_handlers' ) :
            array();
        $this->processHandlers(
            $container,
            $container->getDefinition( 'ezpublish.core.io.binarydata_handler.factory' ),
            $ioBinarydataHandlers,
            $container->getParameter( 'ez_io.available_binarydata_handler_types' ),
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
    protected function processHandlers(
        ContainerBuilder $container,
        Definition $factory,
        array $handlers,
        array $handlerTypesMap,
        $defaultHandler
    )
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
                $handlerId = sprintf( '%s.%s', $parentHandlerId, $name );

                $definition = $container->setDefinition( $handlerId, new DefinitionDecorator( $parentHandlerId ) );

                if ( $type === 'flysystem' )
                {
                    $filesystemId = $this->createFlysystemFilesystem( $container, $name, $config['adapter'] );
                    $definition->replaceArgument( 0, new Reference( $filesystemId ) );
                }

                $handlersMap[$name] = $handlerId;
            }
        }

        $factory->addMethodCall( 'setHandlersMap', array( $handlersMap ) );
    }

    /**
     * Creates a flysystem filesystem $name service
     *
     * @param ContainerBuilder $container
     * @param string $name filesystem name (nfs, local...)
     * @param string $adapter adapter name
     *
     * @return string
     */
    private function createFlysystemFilesystem( ContainerBuilder $container, $name, $adapter )
    {
        $adapterId = sprintf( 'oneup_flysystem.%s_adapter', $adapter );
        if ( !$container->hasDefinition( $adapterId ) )
        {
            throw new InvalidConfigurationException( "Unknown flysystem adapter $adapter" );
        }

        $filesystemId = sprintf( 'ezpublish.core.io.flysystem.%s_filesystem', $name );
        $definition = $container->setDefinition(
            $filesystemId,
            new DefinitionDecorator( 'ezpublish.core.io.flysystem.base_filesystem' )
        );
        $definition->setArguments( array( new Reference( $adapterId ) ) );

        return $filesystemId;
    }
}
