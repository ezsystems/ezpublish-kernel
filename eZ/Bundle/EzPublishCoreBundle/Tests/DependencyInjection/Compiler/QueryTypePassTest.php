<?php

/**
 * File containing the BlockViewPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\QueryTypePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class QueryTypePassTest extends AbstractCompilerPassTestCase
{
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
        $serviceId = 'test.query_type';
        $this->defineQueryTypeService(
            $serviceId,
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryType\TestQueryType'
        );

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [[new Reference($serviceId)]]
        );
    }

    public function testConventionQueryType()
    {
        $this->defineQueryTypeBundle();

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [[new Reference('ezpublish.query_type.convention.querytypebundle_testquerytype')]]
        );
    }

    /**
     * Tests that a QueryType that is declared as a service and named by convention is registered correctly.
     */
    public function testServicePlusConvention()
    {
        $this->defineQueryTypeService(
            'test.query_type',
            'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryType\TestQueryType'
        );
        $this->defineQueryTypeBundle();

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [[new Reference('test.query_type')]]
        );
        $this->assertContainerBuilderNotHasService('ezpublish.query_type.convention.querytypebundle_testquerytype');
    }

    private function defineQueryTypeService($serviceId, $class)
    {
        $def = new Definition();
        $def->addTag('ezpublish.query_type');
        $def->setClass($class);
        $this->setDefinition($serviceId, $def);
    }

    /**
     * Adds to the kernel the path to a stub bundle that contains a QueryType class named by convention
     */
    private function defineQueryTypeBundle()
    {
        $this->setParameter(
            'kernel.bundles',
            ['QueryTypeBundle' => 'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryTypeBundle']
        );
    }
}
