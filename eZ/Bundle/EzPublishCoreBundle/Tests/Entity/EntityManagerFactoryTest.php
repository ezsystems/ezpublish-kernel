<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Bundle\EzPublishCoreBundle\Entity\EntityManagerFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class EntityManagerFactoryTest extends TestCase
{
    private const DEFAULT_ENTITY_MANAGER = 'doctrine.orm.ibexa_default_entity_manager';
    private const INVALID_ENTITY_MANAGER = 'doctrine.orm.ibexa_invalid_entity_manager';
    private const DEFAULT_CONNECTION = 'default';
    private const ENTITY_MANAGERS = [
        'ibexa_default' => self::DEFAULT_ENTITY_MANAGER,
        'ibexa_invalid' => self::INVALID_ENTITY_MANAGER,
    ];

    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /** @var \Symfony\Component\DependencyInjection\ServiceLocator */
    private $serviceLocator;

    public function setUp()
    {
        $this->repositoryConfigurationProvider = $this->getRepositoryConfigurationProvider();
        $this->entityManager = $this->getEntityManager();
        $this->serviceLocator = $this->getServiceLocator();
    }

    public function testGetEntityManager(): void
    {
        $serviceLocator = $this->getServiceLocator();
        $serviceLocator
            ->method('has')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn(true);
        $serviceLocator
            ->method('get')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn($this->getEntityManager());

        $this->repositoryConfigurationProvider
            ->method('getRepositoryConfig')
            ->willReturn([
                'alias' => 'my_repository',
                'storage' => [
                    'connection' => 'default',
                ],
            ]);

        $entityManagerFactory = new EntityManagerFactory(
            $this->repositoryConfigurationProvider,
            $serviceLocator,
            self::DEFAULT_CONNECTION,
            self::ENTITY_MANAGERS
        );

        self::assertEquals($this->getEntityManager(), $entityManagerFactory->getEntityManager());
    }

    public function testGetEntityManagerWillUseDefaultConnection(): void
    {
        $serviceLocator = $this->getServiceLocator();
        $serviceLocator
            ->method('has')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn(true);
        $serviceLocator
            ->method('get')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn($this->entityManager);

        $this->repositoryConfigurationProvider
            ->method('getRepositoryConfig')
            ->willReturn([
                'storage' => [],
            ]);

        $entityManagerFactory = new EntityManagerFactory(
            $this->repositoryConfigurationProvider,
            $serviceLocator,
            self::DEFAULT_CONNECTION,
            self::ENTITY_MANAGERS
        );

        self::assertEquals($this->entityManager, $entityManagerFactory->getEntityManager());
    }

    public function testGetEntityManagerInvalid(): void
    {
        $serviceLocator = $this->getServiceLocator();

        $serviceLocator
            ->method('has')
            ->with(self::INVALID_ENTITY_MANAGER)
            ->willReturn(false);

        $this->repositoryConfigurationProvider
            ->method('getRepositoryConfig')
            ->willReturn([
                'alias' => 'invalid',
                'storage' => [
                    'connection' => 'invalid',
                ],
            ]);

        $entityManagerFactory = new EntityManagerFactory(
            $this->repositoryConfigurationProvider,
            $serviceLocator,
            'default',
            [
                'default' => 'doctrine.orm.default_entity_manager',
            ]
        );

        $this->expectException(InvalidArgumentException::class);

        $entityManagerFactory->getEntityManager();
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRepositoryConfigurationProvider(): RepositoryConfigurationProvider
    {
        return $this->createMock(RepositoryConfigurationProvider::class);
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ServiceLocator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getServiceLocator(): ServiceLocator
    {
        return $this->createMock(ServiceLocator::class);
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
    }
}
