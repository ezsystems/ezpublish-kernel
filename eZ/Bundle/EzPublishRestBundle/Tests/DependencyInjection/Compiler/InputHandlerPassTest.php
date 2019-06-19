<?php

/**
 * File containing the InputHandlerPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\InputHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Reference;

class InputHandlerPassTest extends TestCase
{
    public function testProcess()
    {
        $visitorDefinition = new Definition();
        $visitorDefinition->addTag('ezpublish_rest.input.handler', ['format' => 'test']);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(
            [
                'ezpublish_rest.input.dispatcher' => new Definition(),
                'ezpublish_rest.input.handler.test' => $visitorDefinition,
            ]
        );

        $compilerPass = new InputHandlerPass();
        $compilerPass->process($containerBuilder);

        $dispatcherMethodCalls = $containerBuilder
            ->getDefinition('ezpublish_rest.input.dispatcher')
            ->getMethodCalls();
        self::assertTrue(isset($dispatcherMethodCalls[0][0]), 'Failed asserting that dispatcher has a method call');
        self::assertEquals('addHandler', $dispatcherMethodCalls[0][0], "Failed asserting that called method is 'addParser'");
        self::assertInstanceOf(Reference::class, $dispatcherMethodCalls[0][1][1], 'Failed asserting that method call is to a Reference object');

        self::assertEquals('ezpublish_rest.input.handler.test', $dispatcherMethodCalls[0][1][1]->__toString(), "Failed asserting that Referenced service is 'ezpublish_rest.input.handler.test'");
    }
}
