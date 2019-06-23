<?php

/**
 * File containing the RegisterLimitationTypePassTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler;

use eZ\Publish\Core\Base\Container\Compiler\RegisterLimitationTypePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterLimitationTypePassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.api.repository.factory', new Definition());
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterLimitationTypePass());
    }

    public function testRegisterLimitationType()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.limitationType', ['alias' => $fieldTypeIdentifier]);
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerLimitationType',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterLimitationTypeNoAlias()
    {
        $fieldTypeIdentifier = 'field_type_identifier';
        $serviceId = 'service_id';
        $def = new Definition();
        $def->addTag('ezpublish.limitationType');
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.repository.factory',
            'registerLimitationType',
            [$fieldTypeIdentifier, new Reference($serviceId)]
        );
    }
}
