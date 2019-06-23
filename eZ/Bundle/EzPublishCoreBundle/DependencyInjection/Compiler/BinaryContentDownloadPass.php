<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Injects the downloadUrlGenerator into the binary fieldtype external storage services.
 */
class BinaryContentDownloadPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.fieldType.ezbinarybase.download_url_generator')) {
            return;
        }

        $downloadUrlReference = new Reference('ezpublish.fieldType.ezbinarybase.download_url_generator');

        $this->addCall($container, $downloadUrlReference, 'ezpublish.fieldType.ezmedia.externalStorage');
        $this->addCall($container, $downloadUrlReference, 'ezpublish.fieldType.ezbinaryfile.externalStorage');
    }

    private function addCall(ContainerBuilder $container, Reference $reference, $targetServiceName)
    {
        if (!$container->has($targetServiceName)) {
            return;
        }

        $definition = $container->findDefinition($targetServiceName);
        $definition->addMethodCall('setDownloadUrlGenerator', [$reference]);
    }
}
