<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use Symfony\Component\DependencyInjection\ContainerAware;

class SolrEngineFactory extends ContainerAware
{
    /**
     * @var RepositoryConfigurationProvider
     */
    private $repositoryConfigurationProvider;

    /**
     * @var
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $searchEngineClass;

    public function __construct(RepositoryConfigurationProvider $repositoryConfigurationProvider, $defaultConnection, $searchEngineClass)
    {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->searchEngineClass = $searchEngineClass;
    }

    public function buildEngine()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        $contentHandlerId = $this->container->getParameter("ez_search_engine_solr.connection.$connection.content_handler_id");
        $locationHandlerId = $this->container->getParameter("ez_search_engine_solr.connection.$connection.location_handler_id");

        return new $this->searchEngineClass(
            $this->container->get($contentHandlerId),
            $this->container->get($locationHandlerId)
        );
    }
}
