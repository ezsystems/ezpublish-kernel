<?php

/**
 * File containing the OutputVisitorPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishRestBundle\DependencyInjection\Compiler\OutputVisitorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\Reference;

class OutputVisitorPassTest extends AbstractCompilerPassTestCase
{
    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OutputVisitorPass());
    }

    public function testProcess()
    {
        $stringRegexp = '(^.*/.*$)';
        $stringDefinition = new Definition();
        $stringDefinition->addTag('ezpublish_rest.output.visitor', ['regexps' => 'ezpublish_rest.output.visitor.test.regexps']);
        $this->setParameter('ezpublish_rest.output.visitor.test.regexps', [$stringRegexp]);
        $this->setDefinition('ezpublish_rest.output.visitor.test_string', $stringDefinition);

        $arrayRegexp = '(^application/json$)';
        $arrayDefinition = new Definition();
        $arrayDefinition->addTag('ezpublish_rest.output.visitor', ['regexps' => [$arrayRegexp]]);
        $this->setDefinition('ezpublish_rest.output.visitor.test_array', $arrayDefinition);

        $this->setDefinition('ezpublish_rest.output.visitor.dispatcher', new Definition());

        $this->compile();

        $visitorsInOrder = $this->getVisitorsInRegistrationOrder();

        self::assertEquals('ezpublish_rest.output.visitor.test_string', $visitorsInOrder[0]);
        self::assertEquals('ezpublish_rest.output.visitor.test_array', $visitorsInOrder[1]);
        $this->assertContainerBuilderHasService('ezpublish_rest.output.visitor.test_string');
        $this->assertContainerBuilderHasService('ezpublish_rest.output.visitor.test_array');
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('ezpublish_rest.output.visitor.dispatcher', 'addVisitor', [
            $stringRegexp,
            new Reference('ezpublish_rest.output.visitor.test_string'),
        ]);
        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall('ezpublish_rest.output.visitor.dispatcher', 'addVisitor', [
            $arrayRegexp,
            new Reference('ezpublish_rest.output.visitor.test_array'),
        ]);
    }

    public function testPriority()
    {
        $definitions = [
            'high' => [
                'regexps' => ['(^.*/.*$)'],
                'priority' => 10,
            ],
            'low' => [
                'regexps' => ['(^application/.*$)'],
                'priority' => -10,
            ],
            'normal_defined' => [
                'regexps' => ['(^application/json$)'],
                'priority' => 0,
            ],
            'normal' => [
                'regexps' => ['(^application/xml$)'],
            ],
        ];

        $expectedPriority = [
            'high',
            'normal_defined',
            'normal',
            'low',
        ];

        $this->setDefinition('ezpublish_rest.output.visitor.dispatcher', new Definition());

        foreach ($definitions as $name => $data) {
            $definition = new Definition();
            $definition->addTag('ezpublish_rest.output.visitor', $data);
            $this->setDefinition('ezpublish_rest.output.visitor.test_' . $name, $definition);
        }

        $this->compile();

        $visitorsInOrder = $this->getVisitorsInRegistrationOrder();

        foreach ($expectedPriority as $index => $priority) {
            self::assertEquals('ezpublish_rest.output.visitor.test_' . $priority, $visitorsInOrder[$index]);
        }
    }

    protected function getVisitorsInRegistrationOrder()
    {
        $calls = $this->container->getDefinition('ezpublish_rest.output.visitor.dispatcher')->getMethodCalls();

        return array_map(function ($call) {
            return (string) $call[1][1];
        }, $calls);
    }
}
