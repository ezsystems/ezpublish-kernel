<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\EntityMangerFactoryServiceLocatorPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class EntityManagerFactoryServiceLocatorPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setDefinition(
            'ibexa.doctrine.orm.entity_manager_factory',
            new Definition(null, [
                '$repositoryConfigurationProvider' => new Reference('ezpublish.api.repository_configuration_provider'),
                '$defaultConnection' => '%doctrine.default_connection%',
                '$entityManagers' => '%doctrine.entity_managers%',
            ])
        );
        $this->setParameter('doctrine.entity_managers', [
            'default' => 'doctrine.orm.default_entity_manager',
            'ibexa_second_connection' => 'doctrine.orm.ibexa_second_connection_entity_manager',
        ]);
        $this->setParameter('doctrine.default_connection', 'default');
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EntityMangerFactoryServiceLocatorPass());
    }

    public function testAddServiceLocatorArgument(): void
    {
        $this->compile();

        $definition = $this->container->getDefinition('ibexa.doctrine.orm.entity_manager_factory');
        $arguments = $definition->getArguments();

        self::assertArrayHasKey('$serviceLocator', $arguments);

        $serviceLocatorServiceId = (string) $arguments['$serviceLocator'];

        $expectedEntityManagers = [
            'doctrine.orm.ibexa_second_connection_entity_manager' => new ServiceClosureArgument(
                new Reference('doctrine.orm.ibexa_second_connection_entity_manager')
            ),
        ];

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            $serviceLocatorServiceId,
            0,
            $expectedEntityManagers
        );
    }
}
