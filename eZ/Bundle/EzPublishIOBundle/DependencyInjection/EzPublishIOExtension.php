<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use ArrayObject;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Validator\Tests\Fixtures\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzPublishIOExtension extends Extension
{
    /**
     * @var ConfigurationFactory[]|ArrayObject
     */
    private $metadataHandlerFactories;

    /**
     * @var ConfigurationFactory[]|ArrayObject
     */
    private $binarydataHandlerFactories;

    public function __construct()
    {
        $this->metadataHandlerFactories = new ArrayObject();
        $this->binarydataHandlerFactories = new ArrayObject();
    }

    /**
     * Registers a metadata handler configuration $factory for handler with $alias
     *
     * @param string $alias
     * @param ConfigurationFactory $factory
     */
    public function addMetadataHandlerFactory( $alias, ConfigurationFactory $factory )
    {
        $this->metadataHandlerFactories[$alias] = $factory;
    }

    /**
     * Registers a binarydata handler configuration $factory for handler with $alias
     *
     * @param string $alias
     * @param ConfigurationFactory $factory
     */
    public function addBinarydataHandlerFactory( $alias, ConfigurationFactory $factory )
    {
        $this->binarydataHandlerFactories[$alias] = $factory;
    }

    /**
     * @return ConfigurationFactory[]|ArrayObject
     */
    public function getMetadataHandlerFactories()
    {
        return $this->metadataHandlerFactories;
    }

    /**
     * @return ConfigurationFactory[]|ArrayObject
     */
    public function getBinarydataHandlerFactories()
    {
        return $this->binarydataHandlerFactories;
    }

    public function getAlias()
    {
        return 'ez_io';
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );

        $configuration = $this->getConfiguration( $configs, $container );

        $config = $this->processConfiguration( $configuration, $configs );

        $loader->load( 'io.yml' );
        $loader->load( 'default_settings.yml' );

        $this->processHandlers( $container, $config, 'metadata_handlers' );
        $this->processHandlers( $container, $config, 'binarydata_handlers' );
    }

    /**
     * Processes the config key $key, and registers the result in ez_io.$key.
     * @param ContainerBuilder $container
     * @param string $key Configuration key, either binarydata or metadata
     */
    private function processHandlers( ContainerBuilder $container, $config, $key )
    {
        $handlers = array();
        if ( isset( $config[$key] ) )
        {
            foreach ( $config[$key] as $name => $config )
            {
                list( $type, $config ) = each( $config );
                if ( isset( $handlers[$name] ) )
                {
                    throw new InvalidConfigurationException( "A $key named $name already exists" );
                }
                $config['type'] = $type;
                $config['name'] = $name;
                $handlers[$name] = $config;
            }
        }
        $container->setParameter( "ez_io.{$key}", $handlers );
    }

    public function getConfiguration( array $config, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $configuration->setMetadataHandlerFactories( $this->getMetadataHandlerFactories() );
        $configuration->setBinarydataHandlerFactories( $this->getBinarydataHandlerFactories() );
        return $configuration;
    }

}
