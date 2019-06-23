<?php

/**
 * File containing the Content class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser handling content related config.
 */
class Content extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('content')
                ->info('Content related configuration')
                ->children()
                    ->booleanNode('view_cache')->end()
                    ->booleanNode('ttl_cache')->end()
                    ->scalarNode('default_ttl')->info('Default value for TTL cache, in seconds')->end()
                    ->arrayNode('tree_root')
                        ->canBeUnset()
                        ->children()
                            ->integerNode('location_id')
                                ->info("Root locationId for routing and link generation.\nUseful for multisite apps with one repository.")
                                ->isRequired()
                            ->end()
                            ->arrayNode('excluded_uri_prefixes')
                                ->info("URI prefixes that are allowed to be outside the content tree\n(useful for content sharing between multiple sites).\nPrefixes are not case sensitive")
                                ->example(['/media/images', '/products'])
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (!empty($scopeSettings['content'])) {
            if (isset($scopeSettings['content']['view_cache'])) {
                $contextualizer->setContextualParameter('content.view_cache', $currentScope, $scopeSettings['content']['view_cache']);
            }

            if (isset($scopeSettings['content']['ttl_cache'])) {
                $contextualizer->setContextualParameter('content.ttl_cache', $currentScope, $scopeSettings['content']['ttl_cache']);
            }

            if (isset($scopeSettings['content']['default_ttl'])) {
                $contextualizer->setContextualParameter('content.default_ttl', $currentScope, $scopeSettings['content']['default_ttl']);
            }

            if (isset($scopeSettings['content']['tree_root'])) {
                $contextualizer->setContextualParameter(
                    'content.tree_root.location_id',
                    $currentScope,
                    $scopeSettings['content']['tree_root']['location_id']
                );
                if (isset($scopeSettings['content']['tree_root']['excluded_uri_prefixes'])) {
                    $contextualizer->setContextualParameter(
                        'content.tree_root.excluded_uri_prefixes',
                        $currentScope,
                        $scopeSettings['content']['tree_root']['excluded_uri_prefixes']
                    );
                }
            }
        }
    }
}
