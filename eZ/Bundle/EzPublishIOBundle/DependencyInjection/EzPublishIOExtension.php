<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzPublishIOExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );

        $configuration = $this->getConfiguration( $configs, $container );

        // Note: this is where the transformation occurs
        $config = $this->processConfiguration( $configuration, $configs );

        $loader->load( 'services.yml' );
        $loader->load( 'io.yml' );
        $loader->load( 'default_settings.yml' );

        // Map settings
        $processor = new ConfigurationProcessor( $container, 'ez_io' );
        $processor->mapConfig(
            $config,
            function ( $scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
            {
                if ( isset( $scopeSettings['handler'] ) )
                {
                    $contextualizer->setContextualParameter( 'handler', $currentScope, $scopeSettings['handler'] );
                }
            }
        );
    }

    public function getAlias()
    {
        return 'ez_io';
    }
}
