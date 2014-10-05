<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ez_io' );

        $systemNode = $this->generateScopeBaseNode( $rootNode );
        $systemNode
            ->scalarNode( 'handler' )
            ->info( 'IO handler uses to manipulate binary files' )
            ->example( 'legacy_vardir' )
            ->end();

        return $treeBuilder;
    }
}
