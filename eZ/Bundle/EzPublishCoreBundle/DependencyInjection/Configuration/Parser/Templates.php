<?php

/**
 * File containing the Templates class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class Templates extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode(static::NODE_KEY)
                ->info(static::INFO)
                ->prototype('array')
                    ->children()
                        ->scalarNode('template')
                            ->info(static::INFO_TEMPLATE_KEY)
                            ->isRequired()
                        ->end()
                        ->scalarNode('priority')
                            ->defaultValue(0)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        foreach ($config['siteaccess']['groups'] as $group => $saArray) {
            if (!empty($config[$contextualizer->getSiteAccessNodeName()][$group][static::NODE_KEY])) {
                $contextualizer->setContextualParameter(
                    static::NODE_KEY,
                    $group,
                    $config[$contextualizer->getSiteAccessNodeName()][$group][static::NODE_KEY]
                );
            }
        }

        $contextualizer->mapConfigArray(static::NODE_KEY, $config);
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        // Nothing to do here.
    }
}
