<?php
/**
 * File containing the LegacyDbHandlerFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class LegacyDbHandlerFactory extends ContainerAware
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * Hash of registered storage engines.
     * Key is the storage engine identifier, value is its corresponding service Id
     *
     * @var array
     */
    protected $storageEngines = array();

    public function __construct( ConfigResolverInterface $configResolver, array $repositories )
    {
        $this->configResolver = $configResolver;
        $this->repositories = $repositories;
    }

    /**
     * Builds the DB handler used by the legacy storage engine.
     *
     * @throws Exception\InvalidRepositoryException
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    public function buildLegacyDbHandler()
    {
        $repositoryAlias = $this->configResolver->getParameter( 'repository' );
        $repositoryConfig = $this->repositories[$repositoryAlias];
        if ( !isset( $this->repositories[$repositoryAlias] ) )
        {
            throw new InvalidRepositoryException(
                "Undefined repository '$repositoryAlias'. Did you forget to configure it in ezpublish_*.yml?"
            );
        }

        $connectionHandlerClass = $this->container->getParameter( 'ezpublish.api.storage_engine.legacy.dbhandler.class' );
        return new $connectionHandlerClass( $this->container->get( sprintf( 'doctrine.dbal.%s_connection', $repositoryConfig['connection'] ) ) );
    }
}
