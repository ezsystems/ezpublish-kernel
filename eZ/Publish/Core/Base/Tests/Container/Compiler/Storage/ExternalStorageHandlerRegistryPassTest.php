<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Tests\Container\Compiler\Storage;

use eZ\Publish\Core\Base\Container\Compiler\Storage\ExternalStorageHandlerRegistryPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ExternalStorageHandlerRegistryPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->setDefinition('ezpublish.persistence.external_storage_handler.registry', new Definition());
    }

    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExternalStorageHandlerRegistryPass());
    }

    public function testRegisterExternalStoragePersistenceHandler()
    {
        $def = new Definition();
        $storageIdentifier = 'StorageIdentifier';
        $def->addTag('ezpublish.persistence.externalStorageHandler', ['identifier' => $storageIdentifier]
        );
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_handler.registry',
            'register',
            array($storageIdentifier, new Reference($serviceId))
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testRegisterExternalStoragePersistenceHandlerNoIdentifier()
    {
        $def = new Definition();
        $storageIdentifier = 'StorageIdentifier';
        $def->addTag('ezpublish.persistence.externalStorageHandler');
        $serviceId = 'some_service_id';
        $this->setDefinition($serviceId, $def);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'ezpublish.persistence.external_storage_handler.registry',
            'register',
            array($storageIdentifier, new Reference($serviceId))
        );
    }
}
