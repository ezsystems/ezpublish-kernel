<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\ApiLoader;

use eZ\Bundle\EzPublishCoreBundle\ApiLoader\RepositoryConfigurationProvider;
use eZ\Bundle\EzPublishElasticsearchSearchEngineBundle\DependencyInjection\EzPublishElasticsearchSearchEngineExtension;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ElasticsearchEngineFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var string */
    private $defaultConnection;

    /** @var string */
    private $searchEngineClass;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        $defaultConnection,
        $searchEngineClass
    ) {
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

        return new $this->searchEngineClass(
            $this->container->get(sprintf('%s.%s', EzPublishElasticsearchSearchEngineExtension::CONTENT_SEARCH_GATEWAY_ID, $connection)),
            $this->container->get(sprintf('%s.%s', EzPublishElasticsearchSearchEngineExtension::LOCATION_SEARCH_GATEWAY_ID, $connection)),
            $this->container->get('ezpublish.search.elasticsearch.mapper'),
            $this->container->get('ezpublish.search.elasticsearch.extractor'),
            $this->container->getParameter("ez_search_engine_elasticsearch.connection.$connection.location_document_type_identifier"),
            $this->container->getParameter("ez_search_engine_elasticsearch.connection.$connection.location_document_type_identifier")
        );
    }
}
