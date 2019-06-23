<?php

/**
 * File containing the FieldTypeProcessorPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\FieldTypeProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

class FieldTypeProcessorPassTest extends TestCase
{
    public function testProcess()
    {
        $processorDefinition = new Definition();
        $processorDefinition->addTag('ezpublish_rest.field_type_processor', ['alias' => 'test']);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                'ezpublish_rest.field_type_processor_registry' => new Definition(),
                'ezpublish_rest.field_type_processor.test' => $processorDefinition,
            ]
        );

        $compilerPass = new FieldTypeProcessorPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder->getDefinition('ezpublish_rest.field_type_processor_registry')->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('registerProcessor', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addVisitor'");
        self::assertInstanceOf(Reference::class, $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');
        self::assertEquals('ezpublish_rest.field_type_processor.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.output.value_object_visitor.test'");
    }
}
