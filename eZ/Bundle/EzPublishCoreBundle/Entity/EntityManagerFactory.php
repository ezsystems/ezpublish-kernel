<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
class EntityManagerFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        ContainerInterface $container
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->container = $container;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        if (isset($repositoryConfig['storage']['connection'])) {
            $entityManagerId = $this->getEntityManagerServiceId($repositoryConfig['storage']['connection']);
        } else {
            $defaultEntityManagerId = $this->getEntityManagerServiceId($this->container->getParameter('doctrine.default_connection'));
            $entityManagerId = $this->container->has($defaultEntityManagerId)
                ? $defaultEntityManagerId
                : 'doctrine.orm.entity_manager';
        }

        if (!$this->container->has($entityManagerId)) {
            throw new \InvalidArgumentException(
                "Invalid Doctrine Entity Manager '{$entityManagerId}' for Repository '{$repositoryConfig['alias']}'." .
                'Valid Entity Managers are: ' . implode(', ', array_keys($this->container->getParameter('doctrine.entity_managers')))
            );
        }

        return $this->container->get($entityManagerId);
    }

    protected function getEntityManagerServiceId(string $connection): string
    {
        return sprintf('doctrine.orm.ibexa_%s_entity_manager', $connection);
    }
}
