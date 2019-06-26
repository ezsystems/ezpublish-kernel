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
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorAwareInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\Collector\SuggestionCollectorInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Suggestion\ConfigSuggestion;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser handling all basic configuration (aka "Image").
 */
class Image extends AbstractParser implements SuggestionCollectorAwareInterface
{
    /** @var SuggestionCollectorInterface */
    private $suggestionCollector;

    public function setSuggestionCollector(SuggestionCollectorInterface $suggestionCollector)
    {
        $this->suggestionCollector = $suggestionCollector;
    }

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('imagemagick')
                ->info('DEPRECATED.')
                ->children()
                    ->scalarNode('pre_parameters')->info('Parameters that must be run BEFORE the filenames and filters')->end()
                    ->scalarNode('post_parameters')->info('Parameters that must be run AFTER the filenames and filters')->end()
                ->end()
            ->end()
            ->arrayNode('image_variations')
                ->info('Configuration for your image variations (aka "image aliases")')
                ->example(
                    [
                        'my_image_variation' => [
                            'reference' => '~',
                            'filters' => [
                                [
                                    'name' => 'geometry/scaledownonly',
                                    'params' => [400, 350],
                                ],
                            ],
                        ],
                        'my_cropped_variation' => [
                            'reference' => 'my_image_variation',
                            'filters' => [
                                [
                                    'name' => 'geometry/scalewidthdownonly',
                                    'params' => [300],
                                ],
                                [
                                    'name' => 'geometry/crop',
                                    'params' => [300, 300, 0, 0],
                                ],
                            ],
                        ],
                    ]
                )
                ->useAttributeAsKey('variation_name')
                ->normalizeKeys(false)
                ->prototype('array')
                    ->children()
                        ->scalarNode('reference')
                            ->info('Tells the system which original variation to use as reference image. Defaults to original')
                            ->example(['reference' => 'large'])
                        ->end()
                        ->arrayNode('filters')
                            ->info('A list of filters to run, each filter must be supported by the active image converters')
                            ->useAttributeAsKey('name')
                            ->normalizeKeys(false)
                            ->prototype('array')
                                ->info('Array/Hash of parameters to pass to the filter')
                                ->useAttributeAsKey('options')
                                ->beforeNormalization()
                                    ->ifTrue(
                                        function ($v) {
                                            // Check if passed array only contains a "params" key (BC with <=5.3).
                                            return is_array($v) && count($v) === 1 && isset($v['params']);
                                        }
                                    )
                                    ->then(
                                        function ($v) {
                                            // If we have the "params" key, just use the value.
                                            return $v['params'];
                                        }
                                    )
                                ->end()
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                        ->arrayNode('post_processors')
                            ->info('Post processors as defined in LiipImagineBundle. See https://github.com/liip/LiipImagineBundle/blob/master/Resources/doc/filters.md#post-processors')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->useAttributeAsKey('name')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->scalarNode('image_host')
                ->info('Images host. All system images URLs are prefixed with given host if configured.')
                ->example('https://ezplatform.com')
            ->end();
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->mapConfigArray('image_variations', $config);
        $contextualizer->mapSetting('image_host', $config);
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (isset($scopeSettings['imagemagick'])) {
            $suggestion = new ConfigSuggestion(
                '"imagemagick" settings are deprecated. Just remove them from your configuration file.'
            );
            $this->suggestionCollector->addSuggestion($suggestion);
        }
    }
}
