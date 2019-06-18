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
use PHPUnit\Framework\TestCase;

class StorageEngineFactoryTest extends TestCase
{
    public function testRegisterStorageEngine()
    {
        /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider $repositoryConfigurationProvider */
        $repositoryConfigurationProvider = $this
            ->getMockBuilder('eZ\\Bundle\\EzPublishCoreBundle\\ApiLoader\\RepositoryConfigurationProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $factory = new StorageEngineFactory($repositoryConfigurationProvider);

        $storageEngines = [
            'foo' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
            'bar' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
            'baz' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
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
        $expectedStorageEngine = $this->getMock('eZ\Publish\SPI\Persistence\Handler');
        $storageEngines = [
            'foo' => $expectedStorageEngine,
            'bar' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
            'baz' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
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
            'foo' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
            'bar' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
            'baz' => $this->getMock('eZ\Publish\SPI\Persistence\Handler'),
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

        $this->assertSame($this->getMock('eZ\Publish\SPI\Persistence\Handler'), $factory->buildStorageEngine());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected function getConfigResolverMock()
    {
        return $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
    }
}
