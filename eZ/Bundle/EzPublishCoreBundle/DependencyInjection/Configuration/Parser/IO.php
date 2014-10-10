<?php
/**
 * File containing the Languages class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @todo test
 */
class IO extends AbstractParser
{
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'io' )
                ->info( 'Binary storage options' )
                ->children()
                    ->scalarNode( 'metadata_handler' )
                        ->info( 'Handler uses to manipulate IO files metadata' )
                        ->example( 'default' )
                    ->end()
                    ->scalarNode( 'binarydata_handler' )
                        ->info( 'Handler uses to manipulate IO files binarydata' )
                        ->example( 'default' )
                    ->end()
                    ->scalarNode( 'url_prefix' )
                        ->info( 'Prefix added to binary files uris. A host can also be added' )
                        ->example( '$var_dir$/$storage_dir$, http://static.example.com/' )
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( !isset( $scopeSettings['io'] ) )
        {
            return;
        }

        $settings = $scopeSettings['io'];
        if ( isset( $settings['metadata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.metadata_handler', $currentScope, $settings['metadata_handler'] );
        }
        if ( isset( $settings['binarydata_handler'] ) )
        {
            $contextualizer->setContextualParameter( 'io.binarydata_handler', $currentScope, $settings['binarydata_handler'] );
        }
        if ( isset( $settings['url_prefix'] ) )
        {
            $contextualizer->setContextualParameter( 'io.url_prefix', $currentScope, $settings['url_prefix'] );
        }
    }

    /**
     * Post process configuration to add io_root_dir and io_prefix.
     */
    public function postMap( array $config, ContextualizerInterface $contextualizer )
    {
        $container = $contextualizer->getContainer();
        $configResolver = $container->get( 'ezpublish.config.resolver.core' );
        $configResolver->setContainer( $container );

        foreach ( array_merge( array( 'default' ), $config['siteaccess']['list'] ) as $sa )
        {
            $varDir = $configResolver->getParameter( 'var_dir', null, $sa );
            $storageDir = $configResolver->getParameter( 'storage_dir', null, $sa );

            $ioPrefix = "$varDir/$storageDir";
            $ioRootDir = "%ezpublish_legacy.root_dir%/$ioPrefix";
            $container->setParameter( "ezsettings.$sa.io_root_dir", $ioRootDir );
            $container->setParameter( "ezsettings.$sa.io_prefix", $ioPrefix );
        }
    }
}
