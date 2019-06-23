<?php

/**
 * File containing the BlockView class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class BlockView extends View
{
    const NODE_KEY = 'block_view';
    const INFO = 'Template selection settings when displaying a page block (to be used with ezpage field type)';

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
                ->children()
                    ->arrayNode('block')
                        ->useAttributeAsKey('key')
                        ->normalizeKeys(false)
                        ->prototype('array')
                            ->children()
                                ->scalarNode('template')->isRequired()->info('Your template path, as MyBundle:subdir:my_template.html.twig')->end()
                                ->scalarNode('controller')
                                    ->info(
<<<EOT
Use custom controller instead of the default one to display a block matching your rules.
You can use the controller reference notation supported by Symfony.
EOT
                                    )
                                    ->example('MyBundle:MyControllerClass:viewBlock')
                                ->end()
                                ->arrayNode('match')
                                    ->info('Condition matchers configuration')
                                    ->useAttributeAsKey('key')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->beforeNormalization()
                ->always()
                    ->then(
                        // Adding one 'block' level in order to match the other view internal config structure.
                        function ($v) {
                            return ['block' => $v];
                        }
                    )
                ->end()
            ->end();
    }
}
