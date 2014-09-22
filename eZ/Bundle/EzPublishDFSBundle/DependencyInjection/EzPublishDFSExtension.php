<?php

namespace eZ\Bundle\EzPublishDFSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzPublishDFSExtension extends Extension
{
    public function getAlias()
    {
        return 'ez_dfs';
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
        $loader->load('services.yml');

        foreach ( $config as $dfsHandlerName => $dfsHandlerConfig )
        {
            foreach ( $dfsHandlerConfig['binarydata'] as $name => $config )
            {
                // @todo This can probably be checked in configuration
                if ( isset( $binaryDataHandler ) )
                {
                    throw new InvalidConfigurationException( "Only one binarydata handler can be set. Use a dispatcher to set more" );
                }
                $binaryDataHandler = $this->createDFSBinaryDataHandler( $name, $config, $container );
            }
            foreach ( $dfsHandlerConfig['metadata'] as $name => $config )
            {
                // @todo This can probably be checked in configuration
                if ( isset( $metaDataHandler ) )
                {
                    throw new InvalidConfigurationException( "Only one metadata handler can be set. Use a dispatcher to set more" );
                }
                $metaDataHandler = $this->createDFSMetaDataHandler( $name, $config, $container );
            }
            $this->createDFSHandler( $dfsHandlerName, $binaryDataHandler, $metaDataHandler, $container );
        }

        // @todo add sanity checks and helpers (which handlers are defined, etc)
    }

    /**
     * @param string $name DFS handler name
     * @param Reference $metaDataHandler reference to the metadata handler service
     * @param Reference $metaDataHandler reference to the metadata handler service
     * @param ContainerBuilder $container
     * @return Reference Reference to the created service
     */
    private function createDFSHandler( $name, Reference $binaryDataHandler, Reference $metaDataHandler, ContainerBuilder $container )
    {
        $id = sprintf( 'ezpublish.core.io.dfs.io_handler.%s', $name );
        $definition = $container->setDefinition( $id, new DefinitionDecorator( 'ezpublish.core.io.dfs.io_handler' ) );

        // @todo prefix needs to be configured somewhere
        $definition->replaceArgument( 0, $metaDataHandler );
        $definition->replaceArgument( 1, $binaryDataHandler );

        return $id;
    }

    /**
     * Creates a DFS Binary Data Handler
     * @param string $handlerName binary handler name  (filesystem, flysystem...)
     * @param array $config Handler configuration options
     * @param ContainerBuilder $container
     * @return Reference Reference to the binarydata handler that was created
     */
    protected function createDFSBinaryDataHandler( $handlerName, array $config, ContainerBuilder $container )
    {
        $parentId = sprintf( 'ezpublish.core.io.dfs.binarydata_handler.%s', $handlerName );

        if ( !$container->hasDefinition( $parentId ) )
        {
            throw new InvalidConfigurationException( "Unknown DFS binarydata handler $handlerName: service @$parentId not found" );
        }
        // @todo this won't work with filesystem
        $id = sprintf( '%s.%s', $parentId, $config['adapter'] );
        $binaryDataHandlerDefinition = $container->setDefinition( $id, new DefinitionDecorator( $parentId ) );

        // @todo Dude, please...
        if ( $handlerName === 'flysystem' )
        {
            $adapterId = sprintf( 'oneup_flysystem.%s_adapter', $config['adapter'] );
            $binaryDataHandlerDefinition->replaceArgument( 0, new Reference( $adapterId ) );
        }
        else if ( $handlerName == 'filesystem' )
        {
            $binaryDataHandlerDefinition->replaceArgument( 0, $config['root'] );
        }

        if ( isset( $config['url_decorator'] ) )
        {
            foreach ( $config['url_decorator'] as $urlDecoratorType => $urlDecoratorConfig )
            {
                foreach ( $urlDecoratorConfig as $urlDecoratorName => $urlDecoratorSubConfig )
                {
                    $urlDecoratorReference = $this->createUrlDecoratorService(
                        $container,
                        $urlDecoratorType,
                        $urlDecoratorName,
                        $urlDecoratorConfig
                    );

                    $binaryDataHandlerDefinition->replaceArgument( 1, $urlDecoratorReference );
                }
            }
        }

        return new Reference( $id );
    }

    /**
     * Creates a DFS MetaData Handler
     * @param string $handlerName binary handler name  (legacy_dfs_cluster, ...)
     * @param array $config Handler configuration options
     * @param ContainerBuilder $container
     * @return Reference Reference to the metadata handler that was created
     */
    protected function createDFSMetaDataHandler( $handlerName, array $config, ContainerBuilder $container )
    {
        $parentId = sprintf( 'ezpublish.core.io.dfs.metadata_handler.%s', $handlerName );

        if ( !$container->hasDefinition( $parentId ) )
        {
            throw new InvalidConfigurationException( "Unknown DFS metadata handler $handlerName" );
        }
        $id = sprintf( '%s.%s', $parentId, $config['connection'] );
        $definition = $container->setDefinition( $id, new DefinitionDecorator( $parentId ) );

        // @todo Dude, please...
        if ( $handlerName === 'legacy_dfs_cluster' )
        {
            $definition->replaceArgument( 0, new Reference( $config['connection'] ) );
        }

        return new Reference( $id );
    }

    /**
     * @param ContainerBuilder $container
     * @param string $type ex: prefix
     * @param string $name ex: static_prefix
     * @param array $config
     *
     * @return Reference
     */
    protected function createUrlDecoratorService( ContainerBuilder $container, $type, $name, $config )
    {
        $parentId = sprintf( 'ezpublish.core.io.dfs.url_decorator.%s', $type );

        if ( !$container->hasDefinition( $parentId ) )
        {
            throw new InvalidConfigurationException(
                "Unknown DFS url decorator $type: service @$parentId not found"
            );
        }

        $id = sprintf( 'ezpublish.core.io.dfs.url_decorator.%s.', $type, $name );
        if ( $container->hasDefinition( $id ) )
        {
            throw new InvalidConfigurationException(
                "An url decorator of type $type with the name $name already exists"
            );
        }

        $definition = $container->setDefinition( $id, new DefinitionDecorator( $parentId ) );
        switch ( $type )
        {
            case 'prefix':
                $definition->replaceArgument( 0, $config['prefix'] );
                break;
        }

        return new Reference( $id );
    }
