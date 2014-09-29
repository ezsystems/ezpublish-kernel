<?php

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface
     */
    private $mainConfigParser;

    public function __construct( ParserInterface $mainConfigParser )
    {
        $this->mainConfigParser = $mainConfigParser;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root( 'ez_io' );

        $this->mainConfigParser->addSemanticConfig( $this->generateScopeBaseNode( $rootNode ) );

        return $treeBuilder;
    }
}
