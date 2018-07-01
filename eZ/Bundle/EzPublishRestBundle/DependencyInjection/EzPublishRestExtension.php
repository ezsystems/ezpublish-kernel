<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EzPublishRestExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('value_object_visitors.yml');
        $loader->load('input_parsers.yml');
        $loader->load('security.yml');
        $loader->load('default_settings.yml');

        $processor = new ConfigurationProcessor($container, 'ezsettings');
        $processor->mapConfigArray('rest_root_resources', $config);
    }

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('nelmio_cors')) {
            $file = __DIR__ . '/../Resources/config/nelmio_cors.yml';
            $config = Yaml::parse(file_get_contents($file));
            $container->prependExtensionConfig('nelmio_cors', $config);
            $container->addResource(new FileResource($file));
        }

        $this->prependRouterConfiguration($container);
    }

    private function prependRouterConfiguration(ContainerBuilder $container)
    {
        $config = ['router' => ['default_router' => ['non_siteaccess_aware_routes' => ['ezpublish_rest_']]]];
        $container->prependExtensionConfig('ezpublish', $config);
    }
}
