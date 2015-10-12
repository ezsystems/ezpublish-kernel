<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformBehatBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class EzPlatformBehatExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $config, ContainerBuilder $container)
    {
    }

    public function prepend(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/ez_view.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }
}
