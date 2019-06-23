<?php

/**
 * File containing the RichText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\AbstractFieldTypeParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        // for BC setup deprecated configuration
        $this->setupDeprecatedConfiguration($nodeBuilder);

        $nodeBuilder
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

        // RichText Custom Tags configuration (list of Custom Tags enabled for current SiteAccess scope)
        $nodeBuilder
            ->arrayNode('custom_tags')
                ->info('List of RichText Custom Tags enabled for the current scope. The Custom Tags must be defined in ezpublish.ezrichtext.custom_tags Node.')
                ->scalarPrototype()->end()
            ->end();

        // RichText Custom Styles configuration (list of Custom Styles enabled for current SiteAccess scope)
        $nodeBuilder
            ->arrayNode('custom_styles')
                ->info('List of RichText Custom Styles enabled for the current scope. The Custom Styles must be defined in ezpublish.ezrichtext.custom_styles Node.')
                ->scalarPrototype()->end()
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

            if (isset($scopeSettings['fieldtypes']['ezrichtext']['custom_tags'])) {
                $this->validateCustomTagsConfiguration(
                    $contextualizer->getContainer(),
                    $scopeSettings['fieldtypes']['ezrichtext']['custom_tags']
                );
                $contextualizer->setContextualParameter(
                    'fieldtypes.ezrichtext.custom_tags',
                    $currentScope,
                    $scopeSettings['fieldtypes']['ezrichtext']['custom_tags']
                );
            }
            if (isset($scopeSettings['fieldtypes']['ezrichtext']['custom_styles'])) {
                $this->validateCustomStylesConfiguration(
                    $contextualizer->getContainer(),
                    $scopeSettings['fieldtypes']['ezrichtext']['custom_styles']
                );
                $contextualizer->setContextualParameter(
                    'fieldtypes.ezrichtext.custom_styles',
                    $currentScope,
                    $scopeSettings['fieldtypes']['ezrichtext']['custom_styles']
                );
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

    /**
     * Validate SiteAccess-defined Custom Tags configuration against global one.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $enabledCustomTags List of Custom Tags enabled for the current scope/SiteAccess
     */
    private function validateCustomTagsConfiguration(
        ContainerInterface $container,
        array $enabledCustomTags
    ) {
        $definedCustomTags = array_keys(
            $container->getParameter(EzPublishCoreExtension::RICHTEXT_CUSTOM_TAGS_PARAMETER)
        );
        foreach ($enabledCustomTags as $customTagName) {
            if (!in_array($customTagName, $definedCustomTags)) {
                throw new InvalidConfigurationException(
                    "Unknown RichText Custom Tag '{$customTagName}'"
                );
            }
        }
    }

    /**
     * Validate SiteAccess-defined Custom Styles configuration against global one.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $enabledCustomStyles List of Custom Styles enabled for the current scope/SiteAccess
     */
    private function validateCustomStylesConfiguration(
        ContainerInterface $container,
        array $enabledCustomStyles
    ) {
        $definedCustomStyles = array_keys(
            $container->getParameter(EzPublishCoreExtension::RICHTEXT_CUSTOM_STYLES_PARAMETER)
        );
        foreach ($enabledCustomStyles as $customStyleName) {
            if (!in_array($customStyleName, $definedCustomStyles)) {
                throw new InvalidConfigurationException(
                    "Unknown RichText Custom Style '{$customStyleName}'"
                );
            }
        }
    }

    /**
     * Add BC setup for deprecated configuration.
     *
     * Note: kept in separate method for readability.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder
     */
    private function setupDeprecatedConfiguration(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('output_custom_tags')
                ->setDeprecated('DEPRECATED. Configure custom tags using custom_tags node')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    [
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    ]
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
                ->setDeprecated('DEPRECATED. Configure custom tags using custom_tags node')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    [
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    ]
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
                ->setDeprecated('DEPRECATED. Configure custom tags using custom_tags node')
                ->info('Custom XSL stylesheets to use for RichText transformation to HTML5. Useful for "custom tags".')
                ->example(
                    [
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10,
                    ]
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
                ->setDeprecated('DEPRECATED. Configure custom tags using custom_tags node')
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
        ;
    }
}
