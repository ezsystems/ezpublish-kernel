<?php
/**
 * File containing the Image class.
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
                ->normalizeKeys( false )
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
            ->end();
    }

    public function preMap( array $config, ContextualizerInterface $contextualizer )
    {
        $contextualizer->mapConfigArray( 'image_variations', $config );
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( isset( $scopeSettings['imagemagick']['pre_parameters'] ) )
        {
            $contextualizer->setContextualParameter(
                'imagemagick.pre_parameters',
                $currentScope,
                $scopeSettings['imagemagick']['pre_parameters']
            );
        }

        if ( isset( $scopeSettings['imagemagick']['post_parameters'] ) )
        {
            $contextualizer->setContextualParameter(
                'imagemagick.post_parameters',
                $currentScope,
                $scopeSettings['imagemagick']['post_parameters']
            );
        }
    }
}
