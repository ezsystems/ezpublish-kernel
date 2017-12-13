<?php

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;


use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class UrlChecker extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('url_checker')
                ->children()
                    ->arrayNode('handlers')
                        ->prototype('variable')
                        ->end()
                        ->useAttributeAsKey('name')
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (isset($scopeSettings['url_checker']) && !empty($scopeSettings['url_checker']['handlers'])) {
            foreach ($scopeSettings['url_checker']['handlers'] as $name => $options) {
                $contextualizer->setContextualParameter('url_handler.' . $name . '.options', $currentScope, $options);
            }
        }
    }
}
