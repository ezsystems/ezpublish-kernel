<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidSearchEngineIndexer;
use eZ\Publish\SPI\Search\IncrementalIndexer;

/**
 * The search engine indexer factory.
 */
class SearchEngineIndexerFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /**
     * Hash of registered search engine indexers.
     * Key is the search engine identifier, value indexer itself.
     *
     * @var \eZ\Publish\SPI\Search\IncrementalIndexer[]
     */
    protected $searchEngineIndexers = [];

    public function __construct(RepositoryConfigurationProvider $repositoryConfigurationProvider)
    {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * Registers $searchEngineIndexer as a valid search engine indexer with identifier $searchEngineIdentifier.
     *
     * note: It is strongly recommended to register indexer as a lazy service.
     *
     * @param \eZ\Publish\SPI\Search\IncrementalIndexer $searchEngineIndexer
     * @param string $searchEngineIdentifier
     */
    public function registerSearchEngineIndexer(
        IncrementalIndexer $searchEngineIndexer,
        $searchEngineIdentifier
    ): void {
        $this->searchEngineIndexers[$searchEngineIdentifier] = $searchEngineIndexer;
    }

    /**
     * Returns registered search engine indexers.
     *
     * @return \eZ\Publish\SPI\Search\IncrementalIndexer[]
     */
    public function getSearchEngineIndexers(): array
    {
        return $this->searchEngineIndexers;
    }

    /**
     * Build search engine indexer identified by its identifier (the "alias" attribute in the service tag),
     * resolved for current siteaccess.
     *
     * @throws \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidSearchEngineIndexer
     *
     * @return \eZ\Publish\SPI\Search\IncrementalIndexer
     */
    public function buildSearchEngineIndexer(): IncrementalIndexer
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        if (
            !(
                isset($repositoryConfig['search']['engine'])
                && isset($this->searchEngineIndexers[$repositoryConfig['search']['engine']])
            )
        ) {
            throw new InvalidSearchEngineIndexer(
                "Invalid search engine '{$repositoryConfig['search']['engine']}'. " .
                "Could not find a service tagged as 'ezpublish.searchEngineIndexer' " .
                "with alias '{$repositoryConfig['search']['engine']}'."
            );
        }

        return $this->searchEngineIndexers[$repositoryConfig['search']['engine']];
    }
}
