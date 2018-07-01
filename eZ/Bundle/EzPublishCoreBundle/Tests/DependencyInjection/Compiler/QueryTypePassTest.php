<?php

/**
 * File containing the BlockViewPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\QueryTypePass;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryType\TestQueryType;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryTypeBundle;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class QueryTypePassTest extends AbstractCompilerPassTestCase
{
    private static $queryTypeClass = TestQueryType::class;

    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.query_type.registry', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueryTypePass());
    }

    public function testRegisterTaggedQueryType()
    {
        $def = new Definition();
        $def->addTag('ezpublish.query_type');
        $def->setClass(self::$queryTypeClass);
        $serviceId = 'test.query_type';
        $this->setDefinition($serviceId, $def);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [['Test:Test' => new Reference($serviceId)]]
        );
    }

    public function testRegisterTaggedQueryTypeWithClassAsParameter()
    {
        $this->setParameter('query_type_class', self::$queryTypeClass);
        $def = new Definition();
        $def->addTag('ezpublish.query_type');
        $def->setClass('%query_type_class%');
        $serviceId = 'test.query_type';
        $this->setDefinition($serviceId, $def);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [['Test:Test' => new Reference($serviceId)]]
        );
    }

    public function testConventionQueryType()
    {
        $this->setParameter('kernel.bundles', ['QueryTypeBundle' => QueryTypeBundle::class]);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [['Test:Test' => new Reference('ezpublish.query_type.convention.querytypebundle_testquerytype')]]
        );
    }

    public function testConventionSkippedIfTagged()
    {
        $this->setParameter('kernel.bundles', ['QueryTypeBundle' => QueryTypeBundle::class]);

        $def = new Definition();
        $def->addTag('ezpublish.query_type');
        $def->setClass(self::$queryTypeClass);
        $serviceId = 'test.query_type';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderNotHasService('ezpublish.query_type.convention.querytypebundle_testquerytype');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [['Test:Test' => new Reference($serviceId)]]
        );
    }

    /**
     * Tests query type name override using the 'alias' tag attribute.
     *
     * The QueryType class will still be registered, as the aliases are different from the
     * built-in alias of the class.
     */
    public function testTaggedOverride()
    {
        $this->setParameter('kernel.bundles', ['QueryTypeBundle' => QueryTypeBundle::class]);

        $def = new Definition();
        $def->addTag('ezpublish.query_type', ['alias' => 'overridden_type']);
        $def->setClass(self::$queryTypeClass);
        $this->setDefinition('test.query_type_override', $def);

        $def = new Definition();
        $def->addTag('ezpublish.query_type', ['alias' => 'other_overridden_type']);
        $def->setClass(self::$queryTypeClass);
        $this->setDefinition('test.query_type_other_override', $def);

        $this->compile();

        $this->assertContainerBuilderHasService('ezpublish.query_type.convention.querytypebundle_testquerytype');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [
                [
                    'overridden_type' => new Reference('test.query_type_override'),
                    'other_overridden_type' => new Reference('test.query_type_other_override'),
                    'Test:Test' => new Reference('ezpublish.query_type.convention.querytypebundle_testquerytype'),
                ],
            ]
        );
    }
}
