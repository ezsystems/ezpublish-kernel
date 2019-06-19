<?php

/**
 * File containing the SignalSlotPassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\URLHandlerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class URLHandlerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.url_checker.handler_registry', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new URLHandlerPass());
    }

    public function testRegisterURLHandler()
    {
        $serviceId = 'service_id';
        $scheme = 'http';
        $definition = new Definition();
        $definition->addTag('ezpublish.url_handler', ['scheme' => $scheme]);
        $this->setDefinition($serviceId, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.url_checker.handler_registry',
            'addHandler',
            [$scheme, new Reference($serviceId)]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterURLHandlerNoScheme()
    {
        $serviceId = 'service_id';
        $scheme = 'http';
        $definition = new Definition();
        $definition->addTag('ezpublish.url_handler');
        $this->setDefinition($serviceId, $definition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.url_checker.handler_registry',
            'addHandler',
            [$scheme, new Reference($serviceId)]
        );
    }
}
