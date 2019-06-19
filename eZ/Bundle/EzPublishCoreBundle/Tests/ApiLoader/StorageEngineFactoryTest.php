<?php

/**
 * File containing the StorageEngineFactoryTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageEngineFactory;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use PHPUnit\Framework\TestCase;

class StorageEngineFactoryTest extends TestCase
{
    public function testRegisterStorageEngine()
    {
        /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider $repositoryConfigurationProvider */
        $repositoryConfigurationProvider = $this->createMock(RepositoryConfigurationProvider::class);
        $factory = new StorageEngineFactory($repositoryConfigurationProvider);

        $storageEngines = [
            'foo' => $this->getPersistenceHandlerMock(),
            'bar' => $this->getPersistenceHandlerMock(),
            'baz' => $this->getPersistenceHandlerMock(),
        ];

        foreach ($storageEngines as $identifier => $persistenceHandler) {
            $factory->registerStorageEngine($persistenceHandler, $identifier);
        }

        $this->assertSame($storageEngines, $factory->getStorageEngines());
    }

    public function testBuildStorageEngine()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositories = [
            $repositoryAlias => [
                'storage' => [
                    'engine' => 'foo',
                ],
            ],
            'another' => [
                'storage' => [
                    'engine' => 'bar',
                ],
            ],
        ];
        $expectedStorageEngine = $this->getPersistenceHandlerMock();
        $storageEngines = [
            'foo' => $expectedStorageEngine,
            'bar' => $this->getPersistenceHandlerMock(),
            'baz' => $this->getPersistenceHandlerMock(),
        ];
        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageEngineFactory($repositoryConfigurationProvider);
        foreach ($storageEngines as $identifier => $persistenceHandler) {
            $factory->registerStorageEngine($persistenceHandler, $identifier);
        }

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue($repositoryAlias));

        $this->assertSame($expectedStorageEngine, $factory->buildStorageEngine());
    }

    /**
     * @expectedException \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine
     */
    public function testBuildInvalidStorageEngine()
    {
        $configResolver = $this->getConfigResolverMock();
        $repositoryAlias = 'main';
        $repositories = [
            $repositoryAlias => [
                'storage' => [
                    'engine' => 'undefined_storage_engine',
                ],
            ],
            'another' => [
                'storage' => [
                    'engine' => 'bar',
                ],
            ],
        ];

        $storageEngines = [
            'foo' => $this->getPersistenceHandlerMock(),
            'bar' => $this->getPersistenceHandlerMock(),
            'baz' => $this->getPersistenceHandlerMock(),
        ];

        $repositoryConfigurationProvider = new RepositoryConfigurationProvider($configResolver, $repositories);
        $factory = new StorageEngineFactory($repositoryConfigurationProvider);
        foreach ($storageEngines as $identifier => $persistenceHandler) {
            $factory->registerStorageEngine($persistenceHandler, $identifier);
        }

        $configResolver
            ->expects($this->once())
            ->method('getParameter')
            ->with('repository')
            ->will($this->returnValue($repositoryAlias));

        $this->assertSame($this->getPersistenceHandlerMock(), $factory->buildStorageEngine());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected function getConfigResolverMock()
    {
        return $this->createMock(ConfigResolverInterface::class);
    }

    protected function getPersistenceHandlerMock()
    {
        return $this->createMock(Handler::class);
    }
}
