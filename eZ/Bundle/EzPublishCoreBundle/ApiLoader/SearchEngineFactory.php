<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidSearchEngine;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

/**
 * The search engine factory.
 */
class SearchEngineFactory
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /**
     * Hash of registered search engines.
     * Key is the search engine identifier, value search handler itself.
     *
     * @var \eZ\Publish\SPI\Search\Handler[]
     */
    protected $searchEngines = [];

    public function __construct(RepositoryConfigurationProvider $repositoryConfigurationProvider)
    {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
    }

    /**
     * Registers $searchHandler as a valid search engine with identifier $searchEngineIdentifier.
     *
     * Note It is strongly recommended to register a lazy persistent handler.
     *
     * @param \eZ\Publish\SPI\Search\Handler $searchHandler
     * @param string $searchEngineIdentifier
     */
    public function registerSearchEngine(SearchHandler $searchHandler, $searchEngineIdentifier)
    {
        $this->searchEngines[$searchEngineIdentifier] = $searchHandler;
    }

    /**
     * Returns registered search engines.
     *
     * @return \eZ\Publish\SPI\Search\Handler[]
     */
    public function getSearchEngines()
    {
        return $this->searchEngines;
    }

    /**
     * Builds search engine identified by its identifier (the "alias" attribute in the service tag),
     * resolved for current siteaccess.
     *
     * @throws \eZ\Bundle\EzPublishCoreBundle\ApiLoader\Exception\InvalidSearchEngine
     *
     * @return \eZ\Publish\SPI\Persistence\Handler
     */
    public function buildSearchEngine()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        if (
            !(
                isset($repositoryConfig['search']['engine'])
                && isset($this->searchEngines[$repositoryConfig['search']['engine']])
            )
        ) {
            throw new InvalidSearchEngine(
                "Invalid search engine '{$repositoryConfig['search']['engine']}'. " .
                "Could not find a service tagged as 'ezpublish.searchEngine' " .
                "with alias '{$repositoryConfig['search']['engine']}'."
            );
        }

        return $this->searchEngines[$repositoryConfig['search']['engine']];
    }
}
