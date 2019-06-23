<?php

/**
 * File containing the Image class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

class Page extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('ezpage')
                ->children()
                    ->arrayNode('enabledLayouts')
                        ->prototype('scalar')
                        ->end()
                        ->info('List of enabled layout identifiers')
                    ->end()
                    ->arrayNode('enabledBlocks')
                        ->prototype('scalar')
                        ->end()
                        ->info('List of enabled block identifiers')
                    ->end()
                    ->arrayNode('layouts')
                        ->info('List of registered layouts, the key is the identifier of the layout')
                        ->useAttributeAsKey('key')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->info('Name of the zone type')->end()
                                ->scalarNode('template')->isRequired()->info('Template to use to render this layout')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('blocks')
                        ->info('List of available blocks, the key is the identifier of the block')
                        ->useAttributeAsKey('key')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->children()
                                ->scalarNode('name')->isRequired()->info('Name of the block')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        $container = $contextualizer->getContainer();
        $defaultConfig = [
            'layouts' => $container->getParameter('ezpublish.ezpage.layouts'),
            'blocks' => $container->getParameter('ezpublish.ezpage.blocks'),
            'enabledLayouts' => $container->getParameter('ezpublish.ezpage.enabledLayouts'),
            'enabledBlocks' => $container->getParameter('ezpublish.ezpage.enabledBlocks'),
        ];
        $container->setParameter(
            'ezsettings.' . ConfigResolver::SCOPE_DEFAULT . '.ezpage',
            $defaultConfig
        );

        $contextualizer->mapConfigArray('ezpage', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);

        // filters blocks and layouts for each siteaccess to keep only
        // the enabled ones for this sa
        $configResolver = new ConfigResolver(
            $container->getParameter('ezpublish.siteaccess.groups_by_siteaccess'),
            $container->getParameter('ezpublish.config.default_scope')
        );
        $configResolver->setContainer($container);

        foreach ($config['siteaccess']['list'] as $sa) {
            $siteAccess = new SiteAccess($sa);
            $configResolver->setSiteAccess($siteAccess);
            $ezpageSettings = $configResolver->getParameter('ezpage');
            foreach (['layouts', 'blocks'] as $type) {
                $enabledKey = 'enabled' . ucfirst($type);
                if (empty($ezpageSettings[$enabledKey])) {
                    $ezpageSettings[$type] = [];
                    continue;
                }
                $ezpageSettings[$type] = array_intersect_key(
                    $ezpageSettings[$type],
                    array_flip($ezpageSettings[$enabledKey])
                );
                $ezpageSettings[$enabledKey] = array_unique($ezpageSettings[$enabledKey]);
            }
            $container->setParameter("ezsettings.$sa.ezpage", $ezpageSettings);
        }
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        // Nothing to do here.
    }
}
