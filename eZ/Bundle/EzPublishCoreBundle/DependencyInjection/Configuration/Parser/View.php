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
Params you want to expose in your template as variables.
Key will be the variable name.

Services can be used to return your variables.
EOT
                                )
                                ->example(
                                    array(
                                        "foo"        => "%some.parameter.reference%",
                                        "osTypes"    => array( "osx", "linux", "losedows" ),
                                        "my_service" => '@some_defined_service',
                                        "another_service"   => array(
                                            'service'   => '@another_service',
                                            'method'    => 'getMyVariables'
                                        )
                                    )
                                )
                                ->useAttributeAsKey( "key" )
                                ->prototype( "variable" )
                                    ->beforeNormalization()
                                    ->always()
                                        ->then(
                                            function ( $v )
                                            {
                                                if ( is_string( $v ) )
                                                {
                                                    // Service directly passed (i.e. "@some_defined_service")
                                                    // Assuming it's a ParameterProviderInterface
                                                    if ( $v[0] === '@' )
                                                    {
                                                        $v = array( 'service' => substr( $v, 1 ) );
                                                    }
                                                }
                                                else if ( is_array( $v ) )
                                                {
                                                    if ( isset( $v['service'] ) )
                                                    {
                                                        if ( !is_string( $v['service'] ) || $v['service'][0] !== '@' )
                                                        {
                                                            throw new InvalidArgumentException( 'Configuration error: In ' . static::NODE_KEY . ', an array param with "service" key must be a service identifier prepended by a "@" (e.g. @my_service)' );
                                                        }

                                                        $v['service'] = substr( $v['service'], 1 );
                                                    }
                                                }

                                                return $v;
                                            }
                                        )
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode( 'controller' )
                                ->info(
<<<EOT
Use custom controller instead of the default one to display a content matching your rules.
You can use the controller reference notation supported by Symfony.
EOT
                                )
                                ->example( 'MyBundle:MyControllerClass:view' )
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

