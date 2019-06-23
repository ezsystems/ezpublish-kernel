<?php

/**
 * File containing the Languages class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class Languages extends AbstractParser
{
    private $siteAccessesByLanguages = [];

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('languages')
                ->requiresAtLeastOneElement()
                ->info('Available languages, in order of precedence')
                ->example(['fre-FR', 'eng-GB'])
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('translation_siteaccesses')
                ->info('List of "translation siteaccesses" which can be used by language switcher.')
                ->example(['french_siteaccess', 'english_siteaccess'])
                ->prototype('scalar')->end()
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->mapConfigArray('languages', $config, ContextualizerInterface::UNIQUE);
        $contextualizer->mapConfigArray('translation_siteaccesses', $config, ContextualizerInterface::UNIQUE);

        $container = $contextualizer->getContainer();
        if ($container->hasParameter('ezpublish.siteaccesses_by_language')) {
            $this->siteAccessesByLanguages = $container->getParameter('ezpublish.siteaccesses_by_language');
        }
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        $container = $contextualizer->getContainer();
        if ($container->hasParameter("ezsettings.$currentScope.languages")) {
            $languages = $container->getParameter("ezsettings.$currentScope.languages");
            $mainLanguage = array_shift($languages);
            if ($mainLanguage) {
                $this->siteAccessesByLanguages[$mainLanguage][] = $currentScope;
            }
        }
    }

    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->getContainer()->setParameter(
            'ezpublish.siteaccesses_by_language',
            $this->siteAccessesByLanguages
        );
    }
}
