<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

final class TwigVariablesParser extends AbstractParser
{
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('twig_variables')
                ->info('Contextual Twig variables.')
                ->useAttributeAsKey('name')
                ->normalizeKeys(false)
                ->example([
                    'some' => 'variable',
                    'nested' => [
                        'some' => 'variable',
                        'other' => 123,
                    ],
                ])
                ->prototype('variable')->end()
            ->end();
    }

    public function mapConfig(
        array &$scopeSettings,
        $currentScope,
        ContextualizerInterface $contextualizer
    ) {
        if (!empty($scopeSettings['twig_variables'])) {
            $settings = $scopeSettings['twig_variables'];

            $contextualizer->setContextualParameter(
                'twig_variables',
                $currentScope,
                $settings
            );
        }
    }
}
