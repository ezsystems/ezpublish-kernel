<?php
/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling content related config
 */
class Content extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'content' )
                ->info( 'Content related configuration' )
                ->children()
                    ->booleanNode( 'view_cache' )->defaultValue( true )->end()
                    ->booleanNode( 'ttl_cache' )->defaultValue( true )->end()
                    ->scalarNode( 'default_ttl' )->info( 'Default value for TTL cache, in seconds' )->defaultValue( 60 )->end()
                    ->arrayNode( 'tree_root' )
                        ->canBeUnset()
                        ->children()
                            ->integerNode( 'location_id' )
                                ->info( "Root locationId for routing and link generation.\nUseful for multisite apps with one repository." )
                                ->isRequired()
                            ->end()
                            ->arrayNode( 'excluded_uri_prefixes' )
                                ->info( "URI prefixes that are allowed to be outside the content tree\n(useful for content sharing between multiple sites).\nPrefixes are not case sensitive" )
                                ->example( array( '/media/images', '/products' ) )
                                ->prototype( 'scalar' )->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode( 'fieldtypes' )
                ->children()
                    ->arrayNode( 'ezxml' )
                        ->children()
                            ->arrayNode( 'custom_tags' )
                                ->info( 'Custom XSL stylesheets to use for XmlText transformation to HTML5. Useful for "custom tags".' )
                                ->example(
                                    array(
                                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                                        'priority' => 10
                                    )
                                )
                                ->prototype( 'array' )
                                    ->children()
                                        ->scalarNode( 'path' )
                                            ->info( 'Path of the XSL stylesheet to load.' )
                                            ->isRequired()
                                        ->end()
                                        ->integerNode( 'priority' )
                                            ->info( 'Priority in the loading order. A high value will have higher precedence in overriding XSL templates.' )
                                            ->defaultValue( 0 )
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @return mixed
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        foreach ( $config['system'] as $sa => &$settings )
        {
            if ( !empty( $settings['content'] ) )
            {
                $container->setParameter( "ezsettings.$sa.content.view_cache", $settings['content']['view_cache'] );
                $container->setParameter( "ezsettings.$sa.content.ttl_cache", $settings['content']['ttl_cache'] );
                $container->setParameter( "ezsettings.$sa.content.default_ttl", $settings['content']['default_ttl'] );

                if ( isset( $settings['content']['tree_root'] ) )
                {
                    $container->setParameter( "ezsettings.$sa.content.tree_root.location_id", $settings['content']['tree_root']['location_id'] );
                    if ( isset( $settings['content']['tree_root']['excluded_uri_prefixes'] ) )
                    {
                        $container->setParameter( "ezsettings.$sa.content.tree_root.excluded_uri_prefixes", $settings['content']['tree_root']['excluded_uri_prefixes'] );
                    }
                }
            }

            if ( !empty( $settings['fieldtypes'] ) )
            {
                // Workaround to be able to use registerInternalConfigArray() which only supports first level entries.
                if ( isset( $settings['fieldtypes']['ezxml']['custom_tags'] ) )
                {
                    $settings['fieldtypes.ezxml.custom_xsl'] = $settings['fieldtypes']['ezxml']['custom_tags'];
                    unset( $settings['fieldtypes']['ezxml']['custom_tags'] );
                }
            }
        }

        $this->registerInternalConfigArray( 'fieldtypes.ezxml.custom_xsl', $config, $container );
    }
}
