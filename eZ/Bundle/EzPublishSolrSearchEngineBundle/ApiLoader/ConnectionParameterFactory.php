<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishSolrSearchEngineBundle\ApiLoader;

use Symfony\Component\DependencyInjection\ContainerAware;
use eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * ConnectionParameterFactory will return connection parameters resolved for a current siteaccess.
 */
class ConnectionParameterFactory extends ContainerAware
{
    /**
     * Holds StorageRepositoryProvider, used to get repository configuration
     * resolved for current siteaccess.
     *
     * @var \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider
     */
    protected $repositoryProvider;

    /**
     * Construct from StorageRepositoryProvider
     *
     * @param \eZ\Bundle\EzPublishCoreBundle\ApiLoader\StorageRepositoryProvider $repositoryProvider
     */
    public function __construct( StorageRepositoryProvider $repositoryProvider )
    {
        $this->repositoryProvider = $repositoryProvider;
    }

    /**
     * Returns search engine connection parameter with given $name,
     * resolved for a current siteaccess.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter( $name )
    {
        $defaultConnectionId = "ez_search_engine_solr.default_connection";
        $repositoryConfig = $this->repositoryProvider->getRepositoryConfig();
        $repositoryAlias = $repositoryConfig["alias"];
        $connectionName = $repositoryConfig["search"]["connection"];

        if ( $connectionName === null )
        {
            if ( !$this->container->hasParameter( $defaultConnectionId ) )
            {
                $exception = new InvalidConfigurationException(
                    "Default Solr Search Engine connection is not defined."
                );

                $exception->setPath( "ezpublish.repositories.{$repositoryAlias}.storage" );
                $exception->addHint(
                    "You can define it under 'ez_search_engine_solr' extension, using " .
                    "'default_connection' key. Alternatively, explicitly configure search " .
                    "engine with existing connection name."
                );

                throw $exception;
            }

            $connectionName = $this->container->getParameter( $defaultConnectionId );
        }

        $parameterId = "ez_search_engine_solr.connection.{$connectionName}.{$name}";

        if ( !$this->container->hasParameter( $parameterId ) )
        {
            throw new InvalidConfigurationException(
                "Unknown parameter with id '{$parameterId}'"
            );
        }

        return $this->container->getParameter( $parameterId );
    }
}
