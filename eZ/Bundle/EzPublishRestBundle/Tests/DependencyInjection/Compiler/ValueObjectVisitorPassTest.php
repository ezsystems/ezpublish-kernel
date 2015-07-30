<?php

/**
 * File containing the ValueObjectVisitorPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\ValueObjectVisitorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit_Framework_TestCase;

class ValueObjectVisitorPassTest extends PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag('ezpublish_rest.output.value_object_visitor', array('type' => 'test'));

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            array(
                'ezpublish_rest.output.value_object_visitor.dispatcher' => new Definition(),
                'ezpublish_rest.output.value_object_visitor.test' => $visitorDefinition,
            )
        );

        $compilerPass = new ValueObjectVisitorPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition('ezpublish_rest.output.value_object_visitor.dispatcher')
            ->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('addVisitor', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addVisitor'");
        self::assertInstanceOf('Symfony\\Component\\DependencyInjection\\Reference', $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');

        self::assertEquals('ezpublish_rest.output.value_object_visitor.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.output.value_object_visitor.test'");
    }
}
