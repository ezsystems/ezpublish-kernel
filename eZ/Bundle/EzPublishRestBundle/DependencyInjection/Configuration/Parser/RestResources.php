<?php

namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

class RestResources extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('rest')
                ->children()
                    ->arrayNode('resources')
                        ->useAttributeAsKey('key')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('mediaType')->isRequired()->end()
                                ->scalarNode('href')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        $container = $contextualizer->getContainer();
        $defaultConfig = array(
            'resources' => $container->getParameter('ezpublish_rest.root_resources')
        );
        $container->setParameter(
            'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.rest',
            $defaultConfig
        );

        $contextualizer->mapConfigArray('rest', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        // Nothing to do here.
    }
}
