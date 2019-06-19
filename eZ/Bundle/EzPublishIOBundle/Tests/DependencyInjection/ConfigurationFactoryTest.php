<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractContainerBuilderTestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Abstract class for testing ConfigurationFactory implementations.
 *
 * The part about the container can rely on the matthiasnoback/SymfonyDependencyInjectionTest assertContainer* methods.
 */
abstract class ConfigurationFactoryTest extends AbstractContainerBuilderTestCase
{
    /** @var \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory */
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $this->factory = $this->provideTestedFactory();

        if ($this->factory instanceof ContainerAwareInterface) {
            $this->container = new ContainerBuilder();
            $this->factory->setContainer($this->container);
        }
    }

    public function testGetParentServiceId()
    {
        self::assertEquals(
            $this->provideExpectedParentServiceId(),
            $this->factory->getParentServiceId()
        );
    }

    public function testAddConfiguration()
    {
        $node = new ArrayNodeDefinition('handler');
        $this->factory->addConfiguration($node);
        $this->assertInstanceOf(ArrayNodeDefinition::class, $node);

        // @todo customized testing of configuration node ?
    }

    public function testConfigureHandler()
    {
        $handlerConfiguration =
            $this->provideHandlerConfiguration($this->container) +
            ['name' => 'my_test_handler', 'type' => 'test_handler'];

        $handlerServiceId = $this->registerHandler($handlerConfiguration['name']);

        $this->factory->configureHandler($this->container->getDefinition($handlerServiceId), $handlerConfiguration);

        $this->validateConfiguredHandler($handlerServiceId);

        if ($this->factory instanceof ContainerAwareInterface) {
            $this->validateConfiguredContainer();
        }
    }

    /**
     * Registers the handler in the container, like the pass would have done.
     */
    private function registerHandler($name)
    {
        $this->setDefinition($this->provideExpectedParentServiceId(), $this->provideParentServiceDefinition());
        $handlerServiceId = sprintf('%s.%s', $this->provideExpectedParentServiceId(), $name);
        $this->setDefinition($handlerServiceId, $this->provideParentServiceDefinition());

        return $handlerServiceId;
    }

    /**
     * Returns an instance of the tested factory.
     *
     * @return \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory
     */
    abstract public function provideTestedFactory();

    /**
     * Returns the expected parent service id.
     */
    abstract public function provideExpectedParentServiceId();

    /**
     * Provides the parent service definition, as defined in the bundle's services definition.
     * Required so that getArguments / replaceCalls work correctly.
     *
     * @return Definition
     */
    abstract public function provideParentServiceDefinition();

    /**
     * Provides the configuration array given to the handler, and initializes the container.
     * The name and type index are automatically set to respectively 'my_handler' and 'my_handler_test'.
     *
     * The method can also configure the container via $this->container.
     *
     * @param ContainerBuilder $container
     */
    abstract public function provideHandlerConfiguration();

    /**
     * Lets you test the handler definition after it was configured.
     *
     * Use the assertContainer* methods from matthiasnoback/SymfonyDependencyInjectionTest.
     *
     * @param string $handlerServiceId id of the service that was registered by the compiler pass
     */
    abstract public function validateConfiguredHandler($handlerServiceId);

    /**
     * Lets you test extra changes that may have been done to the container during configuration.
     */
    public function validateConfiguredContainer()
    {
    }
}
