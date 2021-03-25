<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
class EntityManagerFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var \Symfony\Component\DependencyInjection\ServiceLocator */
    private $serviceLocator;

    /** @var string */
    private $defaultConnection;

    /** @var Array<string, string> */
    private $entityManagers;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        ServiceLocator $serviceLocator,
        string $defaultConnection,
        array $entityManagers
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->serviceLocator = $serviceLocator;
        $this->defaultConnection = $defaultConnection;
        $this->entityManagers = $entityManagers;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        if (isset($repositoryConfig['storage']['connection'])) {
            $entityManagerId = $this->getEntityManagerServiceId($repositoryConfig['storage']['connection']);
        } else {
            $defaultEntityManagerId = $this->getEntityManagerServiceId($this->defaultConnection);
            $entityManagerId = $this->serviceLocator->has($defaultEntityManagerId)
                ? $defaultEntityManagerId
                : 'doctrine.orm.entity_manager';
        }

        if (!$this->serviceLocator->has($entityManagerId)) {
            throw new \InvalidArgumentException(
                "Invalid Doctrine Entity Manager '{$entityManagerId}' for Repository '{$repositoryConfig['alias']}'. " .
                'Valid Entity Managers are: ' . implode(', ', array_keys($this->entityManagers))
            );
        }

        return $this->serviceLocator->get($entityManagerId);
    }

    protected function getEntityManagerServiceId(string $connection): string
    {
        return sprintf('doctrine.orm.ibexa_%s_entity_manager', $connection);
    }
}
