<?php

/**
 * File containing the CriterionFieldValueHandlerRegistryPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Search\Legacy;

use eZ\Publish\Core\Base\Container\Compiler\Search\Legacy\CriterionFieldValueHandlerRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CriterionFieldValueHandlerRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition(
            'ezpublish.search.legacy.gateway.criterion_field_value_handler.registry',
            new Definition()
        );
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CriterionFieldValueHandlerRegistryPass());
    }

    public function testRegisterValueHandler()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag(
            'ezpublish.search.legacy.gateway.criterion_field_value_handler',
            ['alias' => $fieldTypeIdentifier]
        );
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.search.legacy.gateway.criterion_field_value_handler.registry',
            'register',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterValueHandlerNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.search.legacy.gateway.criterion_field_value_handler');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.search.legacy.gateway.criterion_field_value_handler.registry',
            'register',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }
}
