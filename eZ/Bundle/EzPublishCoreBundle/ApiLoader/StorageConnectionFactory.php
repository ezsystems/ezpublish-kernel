<?php
/**
 * File containing the StorageConnectionFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;
use InvalidArgumentException;

class StorageConnectionFactory extends ContainerAware
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider
     */
    protected $storageRepositoryProvider;

    public function __construct( StorageRepositoryProvider $storageRepositoryProvider )
    {
        $this->storageRepositoryProvider = $storageRepositoryProvider;
    }

    /**
     * Returns database connection used by database handler
     *
     * @throws \InvalidArgumentException
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        $repositoryConfig = $this->storageRepositoryProvider->getRepositoryConfig();
        // Taking provided connection name if any.
        // Otherwise, just fallback to the default connection.
        if ( isset( $repositoryConfig['connection'] ) )
        {
            $doctrineConnectionId = sprintf( 'doctrine.dbal.%s_connection', $repositoryConfig['connection'] );
        }
        else
        {
            // "database_connection" is an alias to the default connection, set up by DoctrineBundle.
            $doctrineConnectionId = 'database_connection';
        }

        if ( !$this->container->has( $doctrineConnectionId ) )
        {
            throw new InvalidArgumentException(
                "Invalid Doctrine connection '{$repositoryConfig['connection']}' for repository '{$repositoryConfig['alias']}'." .
                "Valid connections are " . implode( ', ', array_keys( $this->container->getParameter( 'doctrine.connections' ) ) )
            );
        }

        return $this->container->get( $doctrineConnectionId );
    }
}
