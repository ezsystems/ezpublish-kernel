<?php

/**
 * File containing the StorageEngineFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidStorageEngine;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;

/**
 * The storage engine factory.
 */
class StorageEngineFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /**
     * Hash of registered storage engines.
     * Key is the storage engine identifier, value persistence handler itself.
     *
     * @var \eZ\Publish\SPI\Persistence\Handler[]
     */
    protected $storageEngines = [];

    public function __construct(RepositoryConfigurationProvider $repositoryConfigurationProvider)
    {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * Registers $persistenceHandler as a valid storage engine, with identifier $storageEngineIdentifier.
     *
     * Note: It is strongly recommenced to register a lazy persistent handler.
     *
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param string $storageEngineIdentifier
     */
    public function registerStorageEngine(PersistenceHandler $persistenceHandler, $storageEngineIdentifier)
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
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        if (
            !(
                isset($repositoryConfig['storage']['engine'])
                && isset($this->storageEngines[$repositoryConfig['storage']['engine']])
            )
        ) {
            throw new InvalidStorageEngine(
                "Invalid storage engine '{$repositoryConfig['storage']['engine']}'. " .
                'Could not find any service tagged as ezpublish.storageEngine ' .
                "with alias {$repositoryConfig['storage']['engine']}."
            );
        }

        return $this->storageEngines[$repositoryConfig['storage']['engine']];
    }
}
