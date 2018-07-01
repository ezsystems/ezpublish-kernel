<?php

/**
 * File containing the EzPublishLegacySearchEngineExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacySearchEngineBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class EzPublishLegacySearchEngineExtension extends Extension
{
    public function getAlias()
    {
        return 'ez_search_engine_legacy';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        // Loading configuration from Core/settings
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../../Publish/Core/settings')
        );
        $loader->load('search_engines/legacy.yml');

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
