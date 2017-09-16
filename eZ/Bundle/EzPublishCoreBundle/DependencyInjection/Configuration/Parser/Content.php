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
use InvalidArgumentException;

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
                    ->booleanNode('view_cache')->defaultValue(true)->end()
                    ->booleanNode('ttl_cache')->defaultValue(true)->end()
                    ->scalarNode('default_ttl')->info('Default value for TTL cache, in seconds')->defaultValue(60)->end()
                    ->arrayNode('tree_root')
                        ->canBeUnset()
                        ->children()
                            ->scalarNode('location_remote_id')
                                ->info("Root locationRemoteId for routing and link generation.\ If set, it will be used instead of location_id. ")
                                ->cannotBeEmpty()
                            ->end()
                            ->integerNode('location_id')
                                ->info("Root locationId for routing and link generation.\nUseful for multisite apps with one repository. It won't be used if location_remote_id is set")
                            ->end()
                            ->arrayNode('excluded_uri_prefixes')
                                ->info("URI prefixes that are allowed to be outside the content tree\n(useful for content sharing between multiple sites).\nPrefixes are not case sensitive")
                                ->example(array('/media/images', '/products'))
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
            $contextualizer->setContextualParameter('content.view_cache', $currentScope, $scopeSettings['content']['view_cache']);
            $contextualizer->setContextualParameter('content.ttl_cache', $currentScope, $scopeSettings['content']['ttl_cache']);
            $contextualizer->setContextualParameter('content.default_ttl', $currentScope, $scopeSettings['content']['default_ttl']);

            if (isset($scopeSettings['content']['tree_root'])) {
                if (isset($scopeSettings['content']['tree_root']['location_id']) &&
                    isset($scopeSettings['content']['tree_root']['location_remote_id'])) {
                    throw new InvalidArgumentException(
                        sprintf("You cannot set location_id and location_remote_id tree_root params for the '%s' siteaccess at the same time", $currentScope)
                    );
                }

                if (isset($scopeSettings['content']['tree_root']['location_id'])) {
                    $contextualizer->setContextualParameter(
                        'content.tree_root.location_id',
                        $currentScope,
                        $scopeSettings['content']['tree_root']['location_id']
                    );
                } elseif (isset($scopeSettings['content']['tree_root']['location_remote_id'])) {
                    $contextualizer->setContextualParameter(
                        'content.tree_root.location_remote_id',
                        $currentScope,
                        $scopeSettings['content']['tree_root']['location_remote_id']
                    );
                }
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
