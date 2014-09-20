<?php

namespace eZ\Bundle\EzPublishDFSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
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
                if ( isset( $binaryDataHandler ) )
                {
                    throw new InvalidConfigurationException( "Only one binarydata handler can be set. Use a dispatcher to set more" );
                }
                $binaryDataHandler = $this->createDFSBinaryDataHandler( $name, $config, $container );
            }
            foreach ( $dfsHandlerConfig['metadata'] as $name => $config )
            {
                // $metaDataHandler = $this->createDFMetaDataHandler( $dfsHandlerName, $name, $config, $container );
            }
            $this->createDFSHandler( $dfsHandlerName, $binaryDataHandler, $container );
        }

        // @todo add sanity checks and helpers (which handlers are defined, etc)
    }

    /**
     * @param string $name
     * @param string $metaDataHandler id of the metadata handler service
     * @param ContainerBuilder $container
     */
    private function createDFSHandler( $name, $metaDataHandler, ContainerBuilder $container )
    {
        $id = sprintf( 'dfs.io_handler.%s', $name );
        $definition = $container->setDefinition( $id, new DefinitionDecorator( 'dfs.io_handler' ) );
        $definition->replaceArgument( 0, new Reference( $metaDataHandler ) );
    }

    /**
     * @param $dfsHandlerConfig
     */
    protected function createDFSBinaryDataHandler( $handlerName, array $config, ContainerBuilder $container )
    {
        $parentId = sprintf( 'dfs.io_handler.binarydata_handler.%s',$handlerName );

        if ( !$container->hasDefinition( $parentId ) )
        {
            throw new InvalidConfigurationException( "Unknown DFS binarydata handler $handlerName" );
        }
        $id = sprintf( '%s.%s', $parentId, $config['adapter'] );
        echo "Creating service $id\n";
        $definition = $container->setDefinition( $id, new DefinitionDecorator( $parentId ) );

        // Dude, please...
        if ( $handlerName === 'flysystem' )
        {
            $adapterId = sprintf( 'oneup_flysystem.%s_adapter', $config['adapter'] );
            $definition->replaceArgument( 0, new Reference( $adapterId ) );
        }
        else if ( $handlerName == 'filesystem' )
        {
            $definition->replaceArgument( 0, $config['root'] );
        }

        return $id;
    }
}
