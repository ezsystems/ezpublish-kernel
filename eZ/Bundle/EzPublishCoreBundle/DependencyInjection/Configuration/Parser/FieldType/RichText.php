<?php

/**
 * File containing the RichText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\AbstractFieldTypeParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

/**
 * Configuration parser handling RichText field type related config.
 */
class RichText extends AbstractFieldTypeParser
{
    /**
     * Returns the fieldType identifier the config parser works for.
     * This is to create the right configuration node under system.<siteaccess_name>.fieldtypes.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'ezrichtext';
    }

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     */
    public function addFieldTypeSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('output_custom_tags')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    array(
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    )
                )
                ->prototype('array')
                    ->children()
                        ->scalarNode('path')
                            ->info('Path of the XSL stylesheet to load.')
                            ->isRequired()
                        ->end()
                        ->integerNode('priority')
                            ->info('Priority in the loading order. A high value will have higher precedence in overriding XSL templates.')
                            ->defaultValue(0)
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('edit_custom_tags')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    array(
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    )
                )
                ->prototype('array')
                    ->children()
                        ->scalarNode('path')
                            ->info('Path of the XSL stylesheet to load.')
                            ->isRequired()
                        ->end()
                        ->integerNode('priority')
                            ->info('Priority in the loading order. A high value will have higher precedence in overriding XSL templates.')
                            ->defaultValue(0)
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('input_custom_tags')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    array(
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    )
                )
                ->prototype('array')
                    ->children()
                        ->scalarNode('path')
                            ->info('Path of the XSL stylesheet to load.')
                            ->isRequired()
                        ->end()
                        ->integerNode('priority')
                            ->info('Priority in the loading order. A high value will have higher precedence in overriding XSL templates.')
                            ->defaultValue(0)
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('tags')
                ->info('RichText template tags configuration.')
                ->useAttributeAsKey('key')
                ->normalizeKeys(false)
                ->prototype('array')
                    ->info(
                        "Name of RichText template tag.\n" .
                        "'default' and 'default_inline' tag names are reserved for fallback."
                    )
                    ->example('math_equation')
                    ->children()
                        ->append(
                            $this->getTemplateNodeDefinition(
                                'Template used for rendering RichText template tag.',
                                'MyBundle:FieldType/RichText/tag:math_equation.html.twig'
                            )
                        )
                        ->variableNode('config')
                            ->info('Tag configuration, arbitrary configuration is allowed here.')
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('embed')
                ->info('RichText embed tags configuration.')
                ->children()
                    ->arrayNode('content')
                        ->info('Configuration for RichText block-level Content embed tags.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText block-level Content embed tags.',
                                    'MyBundle:FieldType/RichText/embed:content.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('content_denied')
                        ->info('Configuration for RichText block-level Content embed tags when embed is not permitted.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText block-level Content embed tags when embed is not permitted.',
                                    'MyBundle:FieldType/RichText/embed:content_denied.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('content_inline')
                        ->info('Configuration for RichText inline-level Content embed tags.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText inline-level Content embed tags.',
                                    'MyBundle:FieldType/RichText/embed:content_inline.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('content_inline_denied')
                        ->info('Configuration for RichText inline-level Content embed tags when embed is not permitted.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText inline-level Content embed tags when embed is not permitted.',
                                    'MyBundle:FieldType/RichText/embed:content_inline_denied.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('location')
                        ->info('Configuration for RichText block-level Location embed tags.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText block-level Location embed tags.',
                                    'MyBundle:FieldType/RichText/embed:location.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('location_denied')
                        ->info('Configuration for RichText block-level Location embed tags when embed is not permitted.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText block-level Location embed tags when embed is not permitted.',
                                    'MyBundle:FieldType/RichText/embed:location_denied.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('location_inline')
                        ->info('Configuration for RichText inline-level Location embed tags.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText inline-level Location embed tags.',
                                    'MyBundle:FieldType/RichText/embed:location_inline.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('location_inline_denied')
                        ->info('Configuration for RichText inline-level Location embed tags when embed is not permitted.')
                        ->children()
                            ->append(
                                $this->getTemplateNodeDefinition(
                                    'Template used for rendering RichText inline-level Location embed tags when embed is not permitted.',
                                    'MyBundle:FieldType/RichText/embed:location_inline_denied.html.twig'
                                )
                            )
                            ->variableNode('config')
                                ->info('Embed configuration, arbitrary configuration is allowed here.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param string $info
     * @param string $example
     *
     * @return \Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition
     */
    protected function getTemplateNodeDefinition($info, $example)
    {
        $templateNodeDefinition = new ScalarNodeDefinition('template');
        $templateNodeDefinition
            ->info($info)
            ->example($example)
            ->isRequired()
            ->cannotBeEmpty();

        return $templateNodeDefinition;
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (!empty($scopeSettings['fieldtypes'])) {
            // Workaround to be able to use Contextualizer::mapConfigArray() which only supports first level entries.
            if (isset($scopeSettings['fieldtypes']['ezrichtext']['output_custom_tags'])) {
                $scopeSettings['fieldtypes.ezrichtext.output_custom_xsl'] = $scopeSettings['fieldtypes']['ezrichtext']['output_custom_tags'];
                unset($scopeSettings['fieldtypes']['ezrichtext']['output_custom_tags']);
            }

            if (isset($scopeSettings['fieldtypes']['ezrichtext']['edit_custom_tags'])) {
                $scopeSettings['fieldtypes.ezrichtext.edit_custom_xsl'] = $scopeSettings['fieldtypes']['ezrichtext']['edit_custom_tags'];
                unset($scopeSettings['fieldtypes']['ezrichtext']['edit_custom_tags']);
            }

            if (isset($scopeSettings['fieldtypes']['ezrichtext']['input_custom_tags'])) {
                $scopeSettings['fieldtypes.ezrichtext.input_custom_xsl'] = $scopeSettings['fieldtypes']['ezrichtext']['input_custom_tags'];
                unset($scopeSettings['fieldtypes']['ezrichtext']['input_custom_tags']);
            }

            if (isset($scopeSettings['fieldtypes']['ezrichtext']['tags'])) {
                foreach ($scopeSettings['fieldtypes']['ezrichtext']['tags'] as $name => $tagSettings) {
                    $contextualizer->setContextualParameter(
                        "fieldtypes.ezrichtext.tags.{$name}",
                        $currentScope,
                        $scopeSettings['fieldtypes']['ezrichtext']['tags'][$name]
                    );
                }
            }

            if (isset($scopeSettings['fieldtypes']['ezrichtext']['embed'])) {
                foreach ($scopeSettings['fieldtypes']['ezrichtext']['embed'] as $type => $embedSettings) {
                    $contextualizer->setContextualParameter(
                        "fieldtypes.ezrichtext.embed.{$type}",
                        $currentScope,
                        $scopeSettings['fieldtypes']['ezrichtext']['embed'][$type]
                    );
                }
            }
        }
    }

    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        $contextualizer->mapConfigArray('fieldtypes.ezrichtext.output_custom_xsl', $config);
        $contextualizer->mapConfigArray('fieldtypes.ezrichtext.edit_custom_xsl', $config);
        $contextualizer->mapConfigArray('fieldtypes.ezrichtext.input_custom_xsl', $config);
    }
}
