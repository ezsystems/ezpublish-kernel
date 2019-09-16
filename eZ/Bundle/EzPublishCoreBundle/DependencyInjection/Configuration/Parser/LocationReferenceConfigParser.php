<?php

declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser for Location References.
 *
 * Example configuration:
 *
 * ```yaml
 * ezpublish:
 *   system:
 *      default: # configuration per siteaccess or siteaccess group
 *          location_references:
 *              media: remote_id("babe4a915b1dd5d369e79adb9d6c0c6a")
 *              # ...
 * ```
 */
final class LocationReferenceConfigParser extends AbstractParser
{
    private const ROOT_NODE_KEY = 'location_references';

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $nodeBuilder
            ->arrayNode(self::ROOT_NODE_KEY)
                ->useAttributeAsKey('name')
                ->scalarPrototype()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        $contextualizer->setContextualParameter(
            'location_references',
            $currentScope,
            $scopeSettings['location_references'] ?? []
        );
    }

    public function postMap(array $config, ContextualizerInterface $contextualizer): void
    {
        $contextualizer->mapConfigArray('location_references', $config);
    }
}
