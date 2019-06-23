<?php

/**
 * Created by PhpStorm.
 * User: lolautruche
 * Date: 19/11/13
 * Time: 08:59.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Compiler;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler\RegisterStorageEnginePass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class RegisterStorageEnginePassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.api.storage_engine.factory', new Definition());
        $this->container->setParameter('ezpublish.api.storage_engine.default', 'default_storage_engine');
    }

    /**
     * Register the compiler pass under test, just like you would do inside a bundle's load()
     * method:.
     *
     *   $container->addCompilerPass(new MyCompilerPass());
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterStorageEnginePass());
    }

    public function testRegisterStorageEngine()
    {
        $storageEngineDef = new Definition();
        $storageEngineIdentifier = 'i_am_a_storage_engine';
        $storageEngineDef->addTag('ezpublish.storageEngine', ['alias' => $storageEngineIdentifier]);
        $serviceId = 'storage_engine_service';
        $this->setDefinition($serviceId, $storageEngineDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.factory',
            'registerStorageEngine',
            [$serviceId, $storageEngineIdentifier]
        );
    }

    public function testRegisterDefaultStorageEngine()
    {
        $storageEngineDef = new Definition();
        $storageEngineIdentifier = 'i_am_a_storage_engine';

        $this->container->setParameter('ezpublish.api.storage_engine.default', $storageEngineIdentifier);
        $storageEngineDef->addTag('ezpublish.storageEngine', ['alias' => $storageEngineIdentifier]);
        $serviceId = 'storage_engine_service';
        $this->setDefinition($serviceId, $storageEngineDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.factory',
            'registerStorageEngine',
            [new Reference($serviceId), $storageEngineIdentifier]
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterStorageEngineNoAlias()
    {
        $storageEngineDef = new Definition();
        $storageEngineIdentifier = 'i_am_a_storage_engine';
        $storageEngineDef->addTag('ezpublish.storageEngine');
        $serviceId = 'storage_engine_service';
        $this->setDefinition($serviceId, $storageEngineDef);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.api.storage_engine.factory',
            'registerStorageEngine',
            [$serviceId, $storageEngineIdentifier]
        );
    }
}
