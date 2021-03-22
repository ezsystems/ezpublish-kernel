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
use Symfony\Component\DependencyInjection\ContainerInterface;

class EntityManagerFactoryTest extends AbstractParamConverterTest
{
    private const DEFAULT_ENTITY_MANAGER = 'doctrine.orm.ibexa_default_entity_manager';
    private const INVALID_ENTITY_MANAGER = 'doctrine.orm.ibexa_invalid_entity_manager';

    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function setUp()
    {
        $this->repositoryConfigurationProvider = $this->getRepositoryConfigurationProvider();
        $this->entityManager = $this->getEntityManager();
        $this->container = $this->getContainer();
    }

    public function testGetEntityManager(): void
    {
        $container = $this->getContainer();
        $container
            ->method('has')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn(true);
        $container
            ->method('get')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn($this->getEntityManager());

        $this->repositoryConfigurationProvider
            ->method('getRepositoryConfig')
            ->willReturn([
                'storage' => [
                    'connection' => 'default',
                ],
            ]);

        $entityManagerFactory = new EntityManagerFactory(
            $this->repositoryConfigurationProvider,
            $container
        );

        self::assertEquals($this->getEntityManager(), $entityManagerFactory->getEntityManager());
    }

    public function testGetEntityManagerWillUseDefaultConnection(): void
    {
        $container = $this->getContainer();
        $container
            ->method('getParameter')
            ->with('doctrine.default_connection')
            ->willReturn('default');
        $container
            ->method('has')
            ->with(self::DEFAULT_ENTITY_MANAGER)
            ->willReturn(true);
        $container
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
            $container
        );

        self::assertEquals($this->entityManager, $entityManagerFactory->getEntityManager());
    }

    public function testGetEntityManagerInvalid(): void
    {
        $container = $this->getContainer();
        $container
            ->method('has')
            ->with(self::INVALID_ENTITY_MANAGER)
            ->willReturn(false);
        $container
            ->method('getParameter')
            ->with('doctrine.entity_managers')
            ->willReturn([
                'default' => 'doctrine.orm.default_entity_manager',
            ]);

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
            $container
        );

        self::expectException(\InvalidArgumentException::class);

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
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->createMock(ContainerInterface::class);
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
    }
}
