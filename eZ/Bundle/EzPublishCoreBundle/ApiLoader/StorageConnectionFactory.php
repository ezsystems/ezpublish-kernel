<?php

/**
 * File containing the StorageConnectionFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use InvalidArgumentException;

class StorageConnectionFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider
     */
    protected $repositoryConfigurationProvider;

    public function __construct(RepositoryConfigurationProvider $repositoryConfigurationProvider)
    {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * Returns database connection used by database handler.
     *
     * @throws \InvalidArgumentException
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();
        // Taking provided connection name if any.
        // Otherwise, just fallback to the default connection.

        if (isset($repositoryConfig['storage']['connection'])) {
            $doctrineConnectionId = sprintf('doctrine.dbal.%s_connection', $repositoryConfig['storage']['connection']);
        } else {
            // "database_connection" is an alias to the default connection, set up by DoctrineBundle.
            $doctrineConnectionId = 'database_connection';
        }

        if (!$this->container->has($doctrineConnectionId)) {
            throw new InvalidArgumentException(
                "Invalid Doctrine connection '{$repositoryConfig['storage']['connection']}' for repository '{$repositoryConfig['alias']}'." .
                'Valid connections are ' . implode(', ', array_keys($this->container->getParameter('doctrine.connections')))
            );
        }

        return $this->container->get($doctrineConnectionId);
    }
}
