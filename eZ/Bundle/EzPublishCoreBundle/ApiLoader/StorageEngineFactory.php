<?php
/**
 * File containing the StorageEngineFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * The storage engine factory.
 */
class StorageEngineFactory
{
    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider
     */
    private $storageRepositoryProvider;

    /**
     * Hash of registered storage engines.
     * Key is the storage engine identifier, value persistence handler itself.
     *
     * @var \eZ\Publish\SPI\Persistence\Handler[]
     */
    protected $storageEngines = array();

    public function __construct( StorageRepositoryProvider $storageRepositoryProvider )
    {
        $this->storageRepositoryProvider = $storageRepositoryProvider;
    }

    /**
     * Registers $persistenceHandler as a valid storage engine, with identifier $storageEngineIdentifier.
     *
     * @note It is strongly recommenced to register a lazy persistent handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param string $storageEngineIdentifier
     */
    public function registerStorageEngine( PersistenceHandler $persistenceHandler, $storageEngineIdentifier )
    {
        $this->storageEngines[$storageEngineIdentifier] = $persistenceHandler;
    }

    /**
     * @return \eZ\Publish\SPI\Persistence\Handler[]
     */
    public function getStorageEngines()
    {
        return $this->storageEngines;
    }

    /**
     * Builds storage engine identified by $storageEngineIdentifier (the "alias" attribute in the service tag).
     *
     * @throws \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function buildStorageEngine()
    {
        $repositoryConfig = $this->storageRepositoryProvider->getRepositoryConfig();

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
