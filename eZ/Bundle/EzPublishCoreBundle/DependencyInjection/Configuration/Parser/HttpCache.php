<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class HttpCache extends AbstractParser
{
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (isset($scopeSettings['http_cache']['purge_servers'])) {
            $contextualizer->setContextualParameter('http_cache.purge_servers', $currentScope, $scopeSettings['http_cache']['purge_servers']);
        }
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder->arrayNode('http_cache')
            ->info('Settings related to Http cache (this kernel defined configuration block will be removed in 7.0. Use ezplatform-http-cache for forward compatibility)')
            ->children()
                ->arrayNode('purge_servers')
                    ->info('Servers to use for Http PURGE (will NOT be used if ezpublish.http_cache.purge_type is "local").')
                    ->example(array('http://localhost/', 'http://another.server/'))
                    ->requiresAtLeastOneElement()
                ->prototype('scalar')->end()
                ->end()
            ->end()
        ->end();
    }
}