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
        $def = new Definition();
        $def->addTag('ezpublish.query_type');
        $def->setClass('eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryType\TestQueryType');
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
        $this->setParameter('kernel.bundles', ['QueryTypeBundle' => 'eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Stub\QueryTypeBundle\QueryTypeBundle']);

        $this->compile();
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.query_type.registry',
            'addQueryTypes',
            [['Test:Test' => new Reference('ezpublish.query_type.convention.querytypebundle_testquerytype')]]
        );
    }
}
