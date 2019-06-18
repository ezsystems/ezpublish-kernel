<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParserInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IO extends AbstractParser
{
    /** @var ComplexSettingParserInterface */
    private $complexSettingParser;

    public function __construct(ComplexSettingParserInterface $complexSettingParser)
    {
        $this->complexSettingParser = $complexSettingParser;
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $nodeBuilder
            ->arrayNode('io')
                ->info('Binary storage options')
                ->children()
                    ->scalarNode('metadata_handler')
                        ->info('Handler uses to manipulate IO files metadata')
                        ->example('default')
                    ->end()
                    ->scalarNode('binarydata_handler')
                        ->info('Handler uses to manipulate IO files binarydata')
                        ->example('default')
                    ->end()
                    ->scalarNode('url_prefix')
                        ->info('Prefix added to binary files uris. A host can also be added')
                        ->example('$var_dir$/$storage_dir$, http://static.example.com/')
                    ->end()
                    ->arrayNode('permissions')
                        ->info('Permissions applied by the Local flysystem adapter when creating content files and directories in storage.')
                        ->children()
                            ->scalarNode('files')
                                ->defaultValue('0644')
                            ->end()
                            ->scalarNode('directories')
                                ->defaultValue('0755')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        if (!isset($scopeSettings['io'])) {
            return;
        }

        $settings = $scopeSettings['io'];
        if (isset($settings['metadata_handler'])) {
            $contextualizer->setContextualParameter('io.metadata_handler', $currentScope, $settings['metadata_handler']);
        }
        if (isset($settings['binarydata_handler'])) {
            $contextualizer->setContextualParameter('io.binarydata_handler', $currentScope, $settings['binarydata_handler']);
        }
        if (isset($settings['url_prefix'])) {
            $contextualizer->setContextualParameter('io.url_prefix', $currentScope, $settings['url_prefix']);
        }
        if (isset($settings['permissions'])) {
            if (isset($settings['permissions']['files'])) {
                $contextualizer->setContextualParameter('io.permissions.files', $currentScope, $settings['permissions']['files']);
            }
            if (isset($settings['permissions']['directories'])) {
                $contextualizer->setContextualParameter('io.permissions.directories', $currentScope, $settings['permissions']['directories']);
            }
        }
    }

    /**
     * Post process configuration to add io_root_dir and io_prefix.
     */
    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        $container = $contextualizer->getContainer();

        // complex parameters dependencies
        foreach (array_merge($config['siteaccess']['list'], array_keys($config['siteaccess']['groups'])) as $scope) {
            $this->addComplexParametersDependencies('io.url_prefix', $scope, $container);
            $this->addComplexParametersDependencies('io.legacy_url_prefix', $scope, $container);
            $this->addComplexParametersDependencies('io.root_dir', $scope, $container);
        }

        // we should only write for default, and for sa/sa groups/global IF they have a declared value
        $scopes = array_merge(
            ['default'],
            $config['siteaccess']['list'],
            array_keys($config['siteaccess']['groups'])
        );
        foreach ($scopes as $scope) {
            // post process io.url_prefix for complex settings
            $postProcessedValue = $this->postProcessComplexSetting('io.url_prefix', $scope, $container);
            if (is_string($postProcessedValue)) {
                $contextualizer->setContextualParameter('io.url_prefix', $scope, $postProcessedValue);
            }

            // post process io.legacy_url_prefix for complex settings
            $postProcessedValue = $this->postProcessComplexSetting('io.legacy_url_prefix', $scope, $container);
            if (is_string($postProcessedValue)) {
                $contextualizer->setContextualParameter('io.legacy_url_prefix', $scope, $postProcessedValue);
            }

            // post process io.root_dir for complex settings
            $postProcessedValue = $this->postProcessComplexSetting('io.root_dir', $scope, $container);
            if (is_string($postProcessedValue)) {
                $contextualizer->setContextualParameter('io.root_dir', $scope, $postProcessedValue);
            }
        }
    }

    /**
     * Applies dependencies of complex $parameter in $scope.
     */
    private function addComplexParametersDependencies($parameter, $scope, ContainerBuilder $container)
    {
        // The complex setting exists in this scope, we don't need to do anything
        if ($container->hasParameter("ezsettings.$scope.$parameter")) {
            return;
        }
        $parameterValue = $container->getParameter("ezsettings.default.$parameter");

        // not complex in this scope
        if (!$this->complexSettingParser->containsDynamicSettings($parameterValue)) {
            return;
        }

        // if one of the complex parameters dependencies is set in the current scope,
        // we set the complex parameter in the current scope as well.
        foreach ($this->complexSettingParser->parseComplexSetting($parameterValue) as $dynamicParameter) {
            $dynamicParameterParts = $this->complexSettingParser->parseDynamicSetting($dynamicParameter);
            if ($dynamicParameterParts['scope'] === $scope) {
                continue;
            }
            $dynamicParameterId = sprintf(
                '%s.%s.%s',
                $dynamicParameterParts['namespace'] ?: 'ezsettings',
                $scope,
                $dynamicParameterParts['param']
            );
            if ($container->hasParameter($dynamicParameterId)) {
                $container->setParameter("ezsettings.$scope.$parameter", $parameterValue);
                break;
            }
        }
    }

    private function postProcessComplexSetting($setting, $sa, ContainerBuilder $container)
    {
        $configResolver = $container->get('ezpublish.config.resolver.core');

        if (!$configResolver->hasParameter($setting, null, $sa)) {
            return false;
        }

        $settingValue = $configResolver->getParameter($setting, null, $sa);
        if (!$this->complexSettingParser->containsDynamicSettings($settingValue)) {
            return false;
        }

        // we kind of need to process this as well, don't we ?
        if ($this->complexSettingParser->isDynamicSetting($settingValue)) {
            $parts = $this->complexSettingParser->parseDynamicSetting($settingValue);
            if (!isset($parts['namespace'])) {
                $parts['namespace'] = 'ezsettings';
            }
            if (!isset($parts['scope'])) {
                $parts['scope'] = $sa;
            }

            return $configResolver->getParameter($parts['param'], null, $sa);
        }

        $value = $settingValue;
        foreach ($this->complexSettingParser->parseComplexSetting($settingValue) as $dynamicSetting) {
            $parts = $this->complexSettingParser->parseDynamicSetting($dynamicSetting);
            if (!isset($parts['namespace'])) {
                $parts['namespace'] = 'ezsettings';
            }
            if (!isset($parts['scope'])) {
                $parts['scope'] = $sa;
            }

            $dynamicSettingValue = $configResolver->getParameter($parts['param'], $parts['namespace'], $parts['scope']);

            $value = str_replace($dynamicSetting, $dynamicSettingValue, $value);
        }

        return $value;
    }
}
