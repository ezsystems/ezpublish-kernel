<?php
/**
 * File containing the Image class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser,
    Symfony\Component\Config\Definition\Builder\NodeBuilder,
    Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Configuration parser handling all basic configuration (aka "Image")
 */
class Image extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'imagemagick' )
                ->children()
                    ->scalarNode( 'pre_parameters' )->info( 'Parameters that must be run BEFORE the filenames and filters' )->end()
                    ->scalarNode( 'post_parameters' )->info( 'Parameters that must be run AFTER the filenames and filters' )->end()
                ->end()
            ->end()
            ->arrayNode( 'image_variations' )
                ->info( 'Configuration for your image variations (aka "image aliases")' )
                ->example(
                    array(
                         'my_image_variation' => array(
                             'reference'    => '~',
                             'filters'      => array(
                                 array(
                                     'name'     => 'geometry/scaledownonly',
                                     'params'   => array( 400, 350 )
                                 )
                             )
                         ),
                         'my_cropped_variation' => array(
                             'reference'    => 'my_cropped_variation',
                             'filters'      => array(
                                 array(
                                     'name'     => 'geometry/scalewidthdownonly',
                                     'params'   => array( 300 )
                                 ),
                                 array(
                                     'name'     => 'geometry/crop',
                                     'params'   => array( 300, 300, 0, 0 )
                                 )
                             )
                         )
                    )
                )
                ->useAttributeAsKey( 'key' )
                ->prototype( 'array' )
                    ->children()
                        ->scalarNode( 'reference' )
                            ->info( 'Tells the system which original variation to use as reference image. Defaults to original' )
                            ->example( array( 'reference' => 'large' ) )
                        ->end()
                        ->arrayNode( 'filters' )
                            ->info( 'A list of filters to run, each filter must be supported by the active image converters' )
                            ->prototype( 'array' )
                                ->children()
                                    ->scalarNode( 'name' )
                                        ->info( 'The filter name, as defined in ImageMagick configuration, or a GD supported filter name' )
                                        ->isRequired()
                                    ->end()
                                    ->arrayNode( 'params' )
                                        ->info( 'Array of parameters to pass to the filter, if needed' )
                                        ->prototype( 'scalar' )->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Translates parsed semantic config values from $config to internal key/value pairs.
     *
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        $this->registerInternalConfigArray( 'image_variations', $config, $container );

        foreach ( $config[$this->baseKey] as $sa => $settings )
        {
            if ( isset( $settings['imagemagick']['pre_parameters'] ) )
                $container->setParameter( "ezsettings.$sa.imagemagick.pre_parameters", $settings['imagemagick']['pre_parameters'] );
            if ( isset( $settings['imagemagick']['post_parameters'] ) )
                $container->setParameter( "ezsettings.$sa.imagemagick.post_parameters", $settings['imagemagick']['post_parameters'] );
        }
    }
}
