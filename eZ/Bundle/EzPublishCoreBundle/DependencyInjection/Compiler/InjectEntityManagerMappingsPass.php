<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Creating Entity Mapping drivers is based on AbstractDoctrineExtension from doctrine/doctrine-bundle.
 * It's required to keep following logic updated with Doctrine changes.
 */
final class InjectEntityManagerMappingsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $entityManagers = $container->getParameter('doctrine.entity_managers');
        $entityMappings = $container->getParameter('ibexa.orm.entity_mappings');

        $mappingDriverConfig = $this->prepareMappingDriverConfig($entityMappings, $container);

        foreach ($entityManagers as $entityManagerName => $serviceName) {
            if (strpos($entityManagerName, 'ibexa_') !== 0) {
                continue;
            }

            $chainMetadataDriverDefinition = $container->getDefinition(
                sprintf('doctrine.orm.%s_metadata_driver', $entityManagerName)
            );

            foreach ($mappingDriverConfig as $driverType => $driverPaths) {
                $metadataDriverServiceName = "doctrine.orm.{$entityManagerName}_{$driverType}_metadata_driver";
                $metadataDriverDefinition = $this->createMetadataDriverDefinition($driverType, $driverPaths);

                if (
                    false !== strpos($metadataDriverDefinition->getClass(), 'yml')
                    || false !== strpos($metadataDriverDefinition->getClass(), 'xml')
                ) {
                    $metadataDriverDefinition->setArguments([array_flip($driverPaths)]);
                    $metadataDriverDefinition->addMethodCall('setGlobalBasename', ['mapping']);
                }

                $container->setDefinition($metadataDriverServiceName, $metadataDriverDefinition);

                foreach ($driverPaths as $prefix => $driverPath) {
                    $chainMetadataDriverDefinition->addMethodCall(
                        'addDriver',
                        [new Reference($metadataDriverServiceName), $prefix]
                    );
                }
            }
        }
    }

    private function createMetadataDriverDefinition($driverType, $driverPaths): Definition
    {
        $metadataDriver = new Definition("%doctrine.orm.metadata.{$driverType}.class%");
        $arguments = [];

        if ('annotation' === $driverType) {
            $arguments[] = new Reference('doctrine.orm.metadata.annotation_reader');
        }

        $arguments[] = array_values($driverPaths);

        $metadataDriver->setArguments($arguments);
        $metadataDriver->setPublic(false);

        return $metadataDriver;
    }

    private function prepareMappingDriverConfig(array $entityManagerConfig, ContainerBuilder $container): array
    {
        $bundles = $container->getParameter('kernel.bundles');
        $driverConfig = [];
        foreach ($entityManagerConfig as $mappingName => $config) {
            $config = array_replace([
                'dir' => false,
                'type' => false,
                'prefix' => false,
            ], (array) $config);

            $config['dir'] = $container->getParameterBag()->resolveValue($config['dir']);

            if ($config['is_bundle']) {
                $bundle = null;
                foreach ($bundles as $bundleName => $class) {
                    if ($mappingName === $bundleName) {
                        $bundle = new \ReflectionClass($class);

                        break;
                    }
                }

                if (null === $bundle) {
                    throw new \InvalidArgumentException(sprintf(
                        'Bundle "%s" does not exist or it is not enabled.',
                        $mappingName)
                    );
                }

                $config = $this->getMappingDriverBundleConfigDefaults($config, $bundle);
            }

            if (!is_dir($config['dir'])) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Doctrine mapping path given. Cannot load Doctrine mapping/bundle named "%s".',
                    $mappingName
                ));
            }

            $driverConfig[$config['type']][$config['prefix']] = realpath($config['dir']) ?: $config['dir'];
        }

        return $driverConfig;
    }

    private function getMappingDriverBundleConfigDefaults(
        array $bundleConfig,
        \ReflectionClass $bundle
    ): array {
        $bundleDir = \dirname($bundle->getFileName());

        if (!$bundleConfig['type'] || !$bundleConfig['dir'] || !$bundleConfig['prefix']) {
            throw new \InvalidArgumentException(
                "Entity Mapping has invalid configuration. Please provide 'type', 'dir' and 'prefix' parameters."
            );
        }

        $bundleConfig['dir'] = $bundleDir . '/' . $bundleConfig['dir'];

        return $bundleConfig;
    }
}
