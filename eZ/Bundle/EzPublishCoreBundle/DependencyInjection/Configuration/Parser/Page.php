<?php
/**
 * File containing the Image class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

/**
 * Configuration parser handling all basic configuration (aka "Image")
 */
class Page extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'ezpage' )
                ->children()
                    ->arrayNode( 'enabledLayouts' )
                        ->prototype( 'scalar' )
                        ->end()
                        ->info( 'List of enabled layout identifiers' )
                    ->end()
                    ->arrayNode( 'enabledBlocks' )
                        ->prototype( 'scalar' )
                        ->end()
                        ->info( 'List of enabled block identifiers' )
                    ->end()
                    ->arrayNode( 'layouts' )
                        ->info( 'List of registered layouts, the key is the identifier of the layout' )
                        ->useAttributeAsKey( 'key' )
                        ->normalizeKeys( false )
                        ->prototype( 'array' )
                            ->children()
                                ->scalarNode( 'name' )->isRequired()->info( 'Name of the zone type' )->end()
                                ->scalarNode( 'template' )->isRequired()->info( 'Template to use to render this layout' )->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode( 'blocks' )
                        ->info( 'List of available blocks, the key is the identifier of the block' )
                        ->useAttributeAsKey( 'key' )
                        ->normalizeKeys( false )
                        ->prototype( 'array' )
                            ->children()
                                ->scalarNode( 'name' )->isRequired()->info( 'Name of the block' )->end()
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
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        $defaultConfig = array(
            'layouts' => $container->getParameter( 'ezpublish.ezpage.layouts' ),
            'blocks' => $container->getParameter( 'ezpublish.ezpage.blocks' ),
            'enabledLayouts' => $container->getParameter( 'ezpublish.ezpage.enabledLayouts' ),
            'enabledBlocks' => $container->getParameter( 'ezpublish.ezpage.enabledBlocks' ),
        );
        $container->setParameter(
            'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.ezpage',
            $defaultConfig
        );
        $this->registerInternalConfigArray(
            'ezpage', $config, $container, self::MERGE_FROM_SECOND_LEVEL
        );

        // filters blocks and layouts for each siteaccess to keep only
        // the enabled ones for this sa
        foreach ( $config['siteaccess']['list'] as $sa )
        {
            $ezpageSettings = $container->getParameter( "ezsettings.$sa.ezpage" );
            foreach ( array( 'layouts', 'blocks' ) as $type )
            {
                $enabledKey = 'enabled' . ucfirst( $type );
                if ( empty( $ezpageSettings[$enabledKey] ) )
                {
                    $ezpageSettings[$type] = array();
                    continue;
                }
                $ezpageSettings[$type] = array_intersect_key(
                    $ezpageSettings[$type],
                    array_flip( $ezpageSettings[$enabledKey] )
                );
            }
            $container->setParameter( "ezsettings.$sa.ezpage", $ezpageSettings );
        }
    }
}
