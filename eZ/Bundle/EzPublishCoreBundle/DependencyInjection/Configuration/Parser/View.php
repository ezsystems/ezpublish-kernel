<?php
/**
 * File containing the View class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use InvalidArgumentException;

class View extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @throws \InvalidArgumentException
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( static::NODE_KEY )
                ->info( static::INFO )
                ->useAttributeAsKey( "key" )
                ->normalizeKeys( false )
                ->prototype( "array" )
                    ->useAttributeAsKey( "key" )
                    ->normalizeKeys( false )
                    ->info( "View selection rulesets, grouped by view type. Key is the view type (e.g. 'full', 'line', ...)" )
                    ->prototype( "array" )
                        ->children()
                            ->scalarNode( "template" )->isRequired()->info( "Your template path, as MyBundle:subdir:my_template.html.twig" )->end()
                            ->arrayNode( "match" )
                                ->info( "Condition matchers configuration" )
                                ->useAttributeAsKey( "key" )
                                ->prototype( "variable" )->end()
                            ->end()
                            ->arrayNode( "params" )
                                ->info(
<<<EOT
Arbitrary params that will be passed in the ContentView object, manageable by ViewProviders.
Those params will NOT be passed to the resulting view template by default.
EOT
                                )
                                ->example(
                                    array(
                                        "foo"        => "%some.parameter.reference%",
                                        "osTypes"    => array( "osx", "linux", "losedows" ),
                                    )
                                )
                                ->useAttributeAsKey( "key" )
                                ->prototype( "variable" )->end()
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
     * @return void
     */
    public function registerInternalConfig( array $config, ContainerBuilder $container )
    {
        $this->registerInternalConfigArray( static::NODE_KEY, $config, $container, self::MERGE_FROM_SECOND_LEVEL );
    }
}
