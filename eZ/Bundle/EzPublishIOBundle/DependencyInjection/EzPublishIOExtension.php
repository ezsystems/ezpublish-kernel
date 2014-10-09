<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

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
     * Array of metadatahandler name => service id
     * @var array
     */
    private $binarydataHandlers = array();

    /**
     * Array of binarydatahandler name => service id
     * @var array
     */
    private $metadataHandlers = array();

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

        // Note: this is where the transformation occurs
        $config = $this->processConfiguration( $configuration, $configs );

        $loader->load( 'io.yml' );
        $loader->load( 'default_settings.yml' );

        if ( isset( $config['binarydata_handlers'] ) )
        {
            foreach ( $config['binarydata_handlers'] as $handlerName => $handlerConfig )
            {
                list( $handlerType, $handlerConfig ) = each( $handlerConfig );
                $this->registerBinarydataHandler( $handlerType, $handlerName, $handlerConfig );
            }
        }

        if ( isset( $config['metadata_handlers'] ) )
        {
            foreach ( $config['metadata_handlers'] as $handlerName => $handlerConfig )
            {
                list( $handlerType, $handlerConfig ) = each( $handlerConfig );
                $this->registerMetadataHandler( $handlerType, $handlerName, $handlerConfig );
            }
        }

        $container->setParameter( 'ez_io.metadata_handlers', $this->metadataHandlers );
        $container->setParameter( 'ez_io.binarydata_handlers', $this->binarydataHandlers );
    }

    private function registerBinaryDataHandler( $type, $name, array $config )
    {
        if ( isset( $this->binarydataHandlers[$type] ) )
        {
            throw new InvalidConfigurationException( "A binarydata handler named $type already exists" );
        }

        $this->binarydataHandlers[$type][$name] = $config;
    }

    private function registerMetaDataHandler( $type, $name, array $config )
    {
        if ( isset( $this->metadataHandlers[$type] ) )
        {
            throw new InvalidConfigurationException( "A metadata handler named $type already exists" );
        }

        $this->metadataHandlers[$type][$name] = $config;
    }
}
