<?php
/**
 * File containing the Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ez_publish_legacy' );
        $rootNode
            ->children()
                ->booleanNode( 'enabled' )->defaultFalse()->end()
                ->scalarNode( 'root_dir' )
                    ->validate()
                        ->ifTrue(
                            function ( $v )
                            {
                                return !file_exists( $v );
                            }
                        )
                        ->thenInvalid( "Provided eZ Publish Legacy root dir does not exist!'" )
                ->end()
            ->end();

        $this->addSiteAccessSettings( $this->generateScopeBaseNode( $rootNode ) );
        return $treeBuilder;
    }

    private function addSiteAccessSettings( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'templating' )
                ->children()
                    ->scalarNode( 'view_layout' )
                        ->info( 'Template reference to use as pagelayout while rendering a content view in legacy' )
                        ->example( 'eZDemoBundle::pagelayout.html.twig' )
                    ->end()
                    ->scalarNode( 'module_layout' )
                        ->info( 'Template reference to use as pagelayout for legacy modules. If not specified, pagelayout from legacy will be used.' )
                    ->end()
                    ->end()
                ->end()
            ->end();
    }
}
