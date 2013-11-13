<?php
/**
 * File containing the BlockView class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class BlockView extends View
{
    const NODE_KEY = "block_view";
    const INFO = "Template selection settings when displaying a page block (to be used with ezpage field type)";

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
            ->arrayNode( static::NODE_KEY )
                ->info( static::INFO )
                ->children()
                    ->arrayNode( 'block' )
                        ->useAttributeAsKey( "key" )
                        ->normalizeKeys( false )
                        ->prototype( "array" )
                            ->children()
                                ->scalarNode( "template" )->isRequired()->info( "Your template path, as MyBundle:subdir:my_template.html.twig" )->end()
                                ->arrayNode( "match" )
                                    ->info( "Condition matchers configuration" )
                                    ->useAttributeAsKey( "key" )
                                    ->prototype( "variable" )->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->beforeNormalization()
                ->always()
                    ->then(
                        // Adding one 'block' level in order to match the other view internal config structure.
                        function ( $v )
                        {
                            return array( 'block' => $v );
                        }
                    )
                ->end()
            ->end();
    }
}
