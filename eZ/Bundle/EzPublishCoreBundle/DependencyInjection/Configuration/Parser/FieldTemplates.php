<?php
/**
 * File containing the FieldTemplates class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

class FieldTemplates implements Parser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'field_templates' )
                ->info( 'Template settings for fields rendered by ez_render_field() Twig function' )
                ->prototype( 'array' )
                    ->children()
                        ->scalarNode( 'template' )
                            ->info( 'Template file where to find block definition to display fields' )
                            ->isRequired()
                        ->end()
                        ->scalarNode( 'priority' )
                            ->defaultValue( 0 )
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.;
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        foreach ( $config['system'] as $sa => $settings )
        {
            if ( !empty( $settings['field_templates'] ) )
            {
                $container->setParameter( "ezsettings.$sa.field_templates", $settings['field_templates'] );
            }
        }
    }


}
