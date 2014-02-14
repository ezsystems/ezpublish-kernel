<?php
/**
 * File containing the StorageEngineFactory class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidRepositoryException;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * The storage engine factory.
 */
class StorageEngineFactory
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var array
     */
    private $repositories;

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
     * Registers $persistenceHandler as a valid storage engine, with identifier $storageEngineIdentifier.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param string $storageEngineIdentifier
     */
    public function registerStorageEngine( PersistenceHandler $persistenceHandler, $storageEngineIdentifier )
    {
        $this->storageEngines[$storageEngineIdentifier] = $persistenceHandler;
    }

    /**
     * Builds storage engine identified by $storageEngineIdentifier (the "alias" attribute in the service tag)
     *
     * @throws Exception\InvalidStorageEngine
     * @throws Exception\InvalidRepositoryException
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function buildStorageEngine()
    {
        $repositoryAlias = $this->configResolver->getParameter( 'repository' );
        if ( !isset( $this->repositories[$repositoryAlias] ) )
        {
            throw new InvalidRepositoryException(
                "Undefined repository '$repositoryAlias'. Did you forget to configure it in ezpublish_*.yml?"
            );
        }

        $repositoryConfig = $this->repositories[$repositoryAlias];

        if (
            !(
                isset( $repositoryConfig['engine'] )
                && isset( $this->storageEngines[$repositoryConfig['engine']] )
            )
        )
        {
            throw new InvalidStorageEngine(
                "Invalid storage engine '{$repositoryConfig['engine']}'. Could not find any service tagged as ezpublish.storageEngine with alias {$repositoryConfig['engine']}."
            );
        }

        return $this->storageEngines[$repositoryConfig['engine']];
    }
}
