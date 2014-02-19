<?php
/**
 * File containing the LegacyDbHandlerFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;
use InvalidArgumentException;

class LegacyDbHandlerFactory extends ContainerAware
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageEngineFactory
     */
    protected $storageEngineFactory;

    public function __construct( StorageEngineFactory $storageEngineFactory )
    {
        $this->storageEngineFactory = $storageEngineFactory;
    }

    /**
     * Builds the DB handler used by the legacy storage engine.
     *
     * @throws \InvalidArgumentException
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    public function buildLegacyDbHandler()
    {
        $repositoryConfig = $this->storageEngineFactory->getRepositoryConfig();
        $doctrineConnectionId = sprintf( 'doctrine.dbal.%s_connection', $repositoryConfig['connection'] );
        if ( !$this->container->has( $doctrineConnectionId ) )
        {
            throw new InvalidArgumentException(
                "Invalid Doctrine connection '{$repositoryConfig['connection']}' for repository '{$repositoryConfig['alias']}'." .
                "Valid connections are " . implode( ', ', array_keys( $this->container->getParameter( 'doctrine.connections' ) ) )
            );
        }

        $connectionHandlerClass = $this->container->getParameter( 'ezpublish.api.storage_engine.legacy.dbhandler.class' );
        return new $connectionHandlerClass( $this->container->get( $doctrineConnectionId ) );
    }
}
