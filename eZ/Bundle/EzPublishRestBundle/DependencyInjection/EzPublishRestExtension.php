<?php
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzPublishRestExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
        $loader->load( 'services.yml' );
        $loader->load( 'value_object_visitors.yml' );
        $loader->load( 'input_parsers.yml' );
        $loader->load( 'security.yml' );
        $loader->load( 'default_settings.yml' );
    }

    public function prepend( ContainerBuilder $container )
    {
        if ( $container->hasExtension( 'nelmio_cors' ) )
        {
            $file = __DIR__ . '/../Resources/config/nelmio_cors.yml';
            $config = Yaml::parse( file_get_contents( $file ) );
            $container->prependExtensionConfig( 'nelmio_cors', $config );
            $container->addResource( new FileResource( $file ) );
        }
    }
}
