<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\InjectEntityManagerMappingsPass;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\AnnotationEntityBundle\AnnotationEntityBundle;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\XmlEntityBundle\XmlEntityBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class InjectEntityManagerMappingPassTest extends AbstractCompilerPassTestCase
{
    private const BUNDLES = [
        'AnnotationEntityBundle' => AnnotationEntityBundle::class,
        'XmlEntityBundle' => XmlEntityBundle::class,
    ];
    private const ENTITY_MANAGERS = ['ibexa_connection' => 'doctrine.orm.ibexa_connection_entity_manager'];
    private const ENTITY_MAPPINGS = [
        'AnnotationEntityBundle' => [
            'is_bundle' => true,
            'type' => 'annotation',
            'dir' => 'Entity',
            'prefix' => '\eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\AnnotationEntityBundle\Entity',
        ],
        'XmlEntityBundle' => [
            'is_bundle' => true,
            'type' => 'xml',
            'dir' => 'config',
            'prefix' => '\eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\XmlEntityBundle\XmlEntityBundle\Entity',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition('doctrine.orm.ibexa_connection_metadata_driver', new Definition());
        $this->setParameter('doctrine.orm.metadata.annotation.class', 'Vendor/Doctrine/Metadata/Driver/AnnotationDriver');
        $this->setParameter('doctrine.orm.metadata.yml.class', 'Vendor/Doctrine/Metadata/Driver/YmlDriver');
        $this->setParameter('doctrine.orm.metadata.xml.class', 'Vendor/Doctrine/Metadata/Driver/XmlDriver');
        $this->setParameter('kernel.bundles', self::BUNDLES);

        $this->setParameter('doctrine.entity_managers', self::ENTITY_MANAGERS);
        $this->setParameter('ibexa.orm.entity_mappings', self::ENTITY_MAPPINGS);
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new InjectEntityManagerMappingsPass());
    }

    public function testInjectEntityMapping(): void
    {
        $this->compile();

        $expectedDriverPaths = [
            'AnnotationEntityBundle' => [
                realpath(__DIR__ . '/../Stub/AnnotationEntityBundle/' . self::ENTITY_MAPPINGS['AnnotationEntityBundle']['dir']),
            ],
            'XmlEntityBundle' => [
                realpath(__DIR__ . '/../Stub/XmlEntityBundle/' . self::ENTITY_MAPPINGS['XmlEntityBundle']['dir']) => sprintf('\\%s\Entity', XmlEntityBundle::class),
            ],
        ];

        foreach (self::ENTITY_MANAGERS as $name => $serviceId) {
            $this->assertContainerBuilderHasService("doctrine.orm.{$name}_metadata_driver");

            foreach (self::ENTITY_MAPPINGS as $mappingName => $config) {
                $metadataDriver = "doctrine.orm.{$name}_{$config['type']}_metadata_driver";
                $this->assertContainerBuilderHasServiceDefinitionWithArgument(
                    $metadataDriver,
                    'annotation' === $config['type'] ? 1 : 0,
                    $expectedDriverPaths[$mappingName]
                );
                $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
                    "doctrine.orm.{$name}_metadata_driver",
                    'addDriver',
                    [new Reference($metadataDriver), $config['prefix']]
                );
            }
        }
    }
}
